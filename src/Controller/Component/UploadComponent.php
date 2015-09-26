<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\Entity;

class UploadComponent extends Component {
	
	protected $_config_ = [
		'private' => true,
		'encrypted' => true,
		'max_size' => '30MB',
		'directory' => '#{WWW_ROOT}/uploads',
		'private_directory' => '#{WWW_ROOT}/uploads/private'
	];
	
	public function initialize(array $config){
		parent::initialize($config);
		$this->mergeConfig($config);
	}
	
	public function getConfig(){
		return $this->_config_;
	}
	
	public function setConfig($config){
		$this->_config_ = $config;
	}
	
	public function mergeConfig($config){
		$this->_config_ = array_merge($this->_config_, $config);
	}
	
	private function _get_encryption($f){
		$encrypt = false;
		if (isset($f['encrypted'])) $encrypt = $f['encrypted'];
		if (!isset($f['iv']) || !isset($f['key'])) $encrypt = false;
		
		return $encrypt;
	}
	
	private function _make_file_encryptor($f){
		$encrypt = $this->_get_encryption($f);
		$key = FileEncryptor::destringify_key($f['key']);
		if ($encrypt){
			$iv = FileEncryptor::destringify_key($f['iv']);
			$encryptor = new FileEncryptor($key, $iv);
		} else {
			$encryptor = new FileEncryptor($key);
		}
		
		return $encryptor;
	}
	
	private function _fix_path($path){
		//Replace #{WWW_ROOT} with WWW_ROOT
		
		//Get WWW_ROOT.
		$www_root = WWW_ROOT;
		
		// If DS isn't /, canoicalise by replacing DS with /
		$ds = trim(DS);
		if ($ds !== '/') $www_root = str_replace($ds,'/',$www_root);
		
		//remove trailing / from WWW_ROOT if there is one
		if (substr($www_root,-1) === '/') $www_root = substr($www_root,0,-1);
		
		//Make replacement
		$fixed_path = preg_replace('/#\{\s*WWW_ROOT\s*\}/', $www_root, $path);
		
		#Remove trailing / from fixed path if there is one
		if (substr($fixed_path,-1) === '/') $fixed_path = substr($fixed_path,0,-1);
		
		return $fixed_path;
	}
	
	private function _public_directory(){
		if (array_key_exists('directory', $this->_config_)){
			$dir = $this->_config_['directory'];
			$dir = $this->_fix_path($dir);
		} else {
			$dir = $this->_fix_path('#{WWW_ROOT}/uploads');
		}
		return $dir;
	}
	
	private function _private_directory(){
		if (array_key_exists('private_directory', $this->_config_)){
			$dir = $this->_config_['private_directory'];
			$dir = $this->_fix_path($dir);
		} else {
			$dir = $this->_public_directory();
			$dir = "$dir/private";
		}
		
		return $dir;
	}
	
	public function parseSize($size_string){
		if (preg_match('/^\s*([0-9.]+)\s*/', $size_string, $matches)){
			$prefix = $matches[1];
		} else {
			$prefix = 0;
		}
		//Cast to int. Note that this will cast values like 0.25M to 0, which
		//is stupid, BUT THIS IS WHAT PHP DOES.
		$number = (int) $prefix;
		
		//Get the suffix. Allow MiB and MB as well as M, etc. Allow case insensitive matching
		$multiplier = 1;
		if (preg_match('/([KMGTP])i?B?\s*$/i', $size_string, $matches)){
			$suffix = strtoupper($matches[1]);
			
			//note the fall through
			switch ($suffix){
				case 'P':
				$multiplier *= 1024;
				
				case 'T':
				$multipler *= 1024;
				
				case 'G':
				$multiplier *= 1024;
				
				case 'M':
				$multiplier *= 1024;
				
				case 'K':
				$multiplier *= 1024;
				
				default:
				break;
			}
		}
		
		$size = $number * $multiplier;
		
		return $size;
	}
	
	public function readableSize($nbytes){
		//Essentially the inverse of parseSize
		$index = floor(log($nbytes, 1024));
		if ($index > 5 || index < 0) $index = 0;
		$suffixes = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
		$converted = $nbytes / pow(1024, $index);
		$suffix = $suffixes[$index];
		$nice = sprintf('%.2f %s', $converted, $suffix);
		return $nice;
		
	}
	
	private function _get_max_size(){
		//Parse the maximum size, return in bytes
		
		$max_user = array_key_exists('max_size', $this->_config_) ? $this->_config_['max_size'] : '30MB';
		
		//Read the maximum size from the php.ini file
		$max_ini = ini_get('upload_max_filesize');
		$max_post_ini = ini_get('post_max_size');
		
		//Parse each of the size values
		if (is_string($max_user)) $max_user = $this->parseSize($max_user);
		$max_ini = $this->parseSize($max_ini);
		$max_post_ini = $this->parseSize($max_post_ini);
		
		$max = max([$max_user, $max_ini, $max_post_ini]);
		
		return $max;
	}
	
	public function generateUploadFileName($f){
		/*
		 * $f = ['key' => key (hex format)
		 * 	'encrypted' =>
		 *  'private' =>
		 *  'iv' =>]
		 */
		$private = isset($f['private']) ? $f['private'] : true;
		
		$encrypt = $this->_get_encryption($f);
		
		$hex_key = $encrypt ? $f['iv'] : $f['key'];
		
		$path = $private ? $this->_private_directory() : $this->_public_directory();
		
		$fn = "$path/$hex_key.aes";
		return $fn;
	}
	
	public function uploadedFileExists($f){
		$fn = $this->generateUploadFileName($f);
		return file_exists($fn);
	}
	
	public function escapeHttpFilename($fn){
		//generate a "nice" filename which is safe for inclusion in
		//a http header - no specials allowed
		$nice_fn = preg_replace('/[^a-zA-Z0-9_\\- .]+/', '', $fn);
		
		return $nice_fn;
	}
	
	public function escapeHttpContentType($content_type){
		//make the content type "safe" for inclusion in headers
		$token = '[^\s()<>@,;:\"/\[\]?.=]+'; //no spaces, no specials: http://www.w3.org/Protocols/rfc1341/4_Content-Type.html
		$type_regex = "%^\s*(application|audio|image|message|multipart|text|video|X-$token)/($token)%";
		if (preg_match($type_regex, $content_type)){
			$safe_content_type = $content_type;
		} else {
			$safe_content_type = 'application/octet-stream';
		}
		return $safe_content_type;
	}
	
    public function uploadFile($f, $private=true,$encrypt=false){
		//Here $f is in the form that comes from PHP $_FILES
		
		$max_size = $this->_get_max_size();
		
		//check for PHP error flag
		if ($f['error'] !== UPLOAD_ERR_OK){
			$success = false;
			switch ($f['error']){
				case \UPLOAD_ERR_NO_FILE:
				$message = 'No file was uploaded';
				break;
				
				case \UPLOAD_ERR_INI_SIZE:
				case \UPLOAD_ERR_FORM_SIZE:
				$nice_size = $this->readableSize($max_size);
				$message = "Size of uploaded file exceeds max file size set on server of $nice_size. Check the setting of upload_max_filesize and post_max_size in php.ini";
				break;
				
				default:
				$message = 'Unknown error. PHP was unable to process upload';
			}
			
			return array('success' => $success, 'message' => $message); 
		}
		
		//check upload size, ensure it is not too big. Allow up to $max_size
		if ($f['size'] > $max_size){
			$nice_size = $this->readableSize($max_size);
			return array('success' => false, 
				'message' => "Uploaded files may not exceed $nice_size");
		}
		
		//generate random file name for encrypted file, which will also
		//be the encryption key
		$iv = call_user_func_array('pack', array_pad(['C*'], 17, 0)); //default is all 0
		$up = array('private' => $private, 'encrypted' => $encrypt);
		do {
			$key = FileEncryptor::generate_key();
			$up['key'] = FileEncryptor::stringify_key($key);
			if ($encrypt) {
				$iv = FileEncryptor::generate_iv();
				$up['iv'] = FileEncryptor::stringify_key($iv);
			}
			$fn = $this->generateUploadFileName($up);
		} while (file_exists($fn));
		
		//encrypt and upload the file. Note that we use a blank IV,
		//since the encryption isn't for the purpose of keeping the
		//contents secret (it's to render the file safe)
		$encryptor = new FileEncryptor($key,$iv);
		$success = $encryptor->encrypt_file_to_file($f['tmp_name'], $fn);
		if ($success === true){
			unlink($f['tmp_name']);
			$message = 'File uploaded and encrypted successfully';
		} else {
			$message = 'Uploaded file could not be encrypted';
		}
		
		//escape the content type, filename which will go in the HTTP
		//headers before storing them in the database. Of course, we will
		//sanitise them on output too, but it's best not to store bad things
		//in the db
		//basename is only really required for windows server
		$nice_fn = $this->escapeHttpFilename($f['name']);
		$safe_content_type = $this->escapeHttpContentType($f['type']);
		
		$ret = array('success' => $success, 'message' => $message,
			'key' => $up['key'], 'name' => $fn, 'file_name' => $nice_fn, 
			'content_type' => $safe_content_type, 'file_size' => $f['size']);
		
		$ret['iv'] = FileEncryptor::stringify_key($iv);
		
		return $ret;
	}
	
	
	public function echoUploadedFile($f, $file_name, $file_size, $file_type){
		$private = isset($f['private']) ? $f['private'] : true;
		
		$encrypt = $this->_get_encryption($f);
		
		//send an uploaded file to the browser, including all the headers
		if (!$this->uploadedFileExists($f)){
			header("HTTP/1.1 404 Not Found");
			die('File not found on server');
		}
		
		//send the headers for the attachment. Escape things before output
		$safe_fn = $this->escapeHttpFilename($file_name);
		$safe_file_type = $this->escapeHttpContentType($file_type);
		$safe_file_size = (int) $file_size;
		
		header('Content-Description: File Transfer');
        header("Content-Type: $safe_file_type");
        header("Content-Disposition: attachment; filename=$safe_fn");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header("Content-Length: $safe_file_size");
		
		//TODO: allow range checking, resume, etc
		//see http://www.media-division.com/php-download-script-with-resume-option/
		//and http://www.media-division.com/the-right-way-to-handle-file-downloads-in-php/
		
		//decrypt the file, and send to browser
		$fn = $this->generateUploadFileName($f);
		$decryptor = $this->_make_file_encryptor($f);
		$ret = $decryptor->decrypt_file_to_browser($fn);
		
		//Don't do any more processing
		exit;
	}
	
	public function decodeUploadedFile($f, $out_fn){
		$encrypt = $this->_get_encryption($f);
		
		$fn = $this->generateUploadFileName($f);
		$decryptor = $this->_make_file_encryptor($f);
		
		$ret = $decryptor->decrypt_file_to_file($fn, $out_fn);
		return $ret;
	}
	
	public function deleteUploadedFile($f){
		$fn = $this->generateUploadFileName($f);
		return unlink($fn);
	}
	
	public function changeUploadedFilePrivacy($f,$new_private=true){
		$old_private = $f['private'];
		if ($old_private ^ $new_private){
			$old_name = $this->generateUploadFileName($f);
			
			$f['private'] = $new_private;
			$new_name = $this->generateUploadFileName($f);
			
			//We need to check that in moving to new_name, we aren't going
			//to over-write anything
			if (!file_exists($new_name)){
				rename($old_name, $new_name);
			} else {
				//We need to re-encrypt the file under a new key and iv
				//decrypt to temp file
				$decryptor = $this->_make_file_encryptor($f);
				$tmp_handle = tmpfile();
				$f_handle = fopen($old_name,'r');
				$decryptor->decrypt($f_handle, $tmp_handle);
				fclose($f_handle);
				
				//generate new name
				do {
					if ($encrypt) {
						$f['iv'] = FileEncryptor::stringify_key(FileEncryptor::generate_iv());
					} else {
						$f['key'] = FileEncryptor::stringify_key(FileEncryptor::generate_key());
					}
					$new_name = $this->generateUploadFileName($f);
				} while (file_exists($new_name));
				
				//re-encrypt the temp file
				fseek($tmp_handle, 0); //rewind file pointer
				$encryptor = $this->_make_file_encryptor($f);
				$f_handle = fopen($new_name,'w');
				$encryptor->encrypt($tmp_handle, $f_handle);
				fclose($f_handle);
				fclose($tmp_handle);
				unlink($old_name);
			}
			
		}
		return $f;
	}
	
	public function attachToEntity(Entity $entity,$file,$options=[]){
		//$file is in format that comes from PHP $_FILE
		//Get the upload options
		 $f = $entity->getAttachmentDescription();
		 
		 //Upload the file
		 $ret = $this->uploadFile($file, $f['private'], $f['encrypted']);
		 
		 //set the file information on the entity
		 if ($ret['success']){
			$entity->attachFile($ret);	
		 }
		 
		 return $ret;
	}
	
	public function detachFromEntity(Entity $entity,$options=[]){
		
		$f = $entity->getAttachmentDescription();
		
		return $this->deleteUploadedFile($f);
	}
	
	public function downloadFromEntity(Entity $entity,$options=[]){
		//get the attached file from the entity, and send it to the browser
		$f = $entity->getAttachmentDescription();
		$file_name = isset($f['file_name']) ? $f['file_name']: 'unnamedfile';
		$content_type = isset($f['content_type']) ? $f['content_type'] : 'application/octet-stream';
		if (isset($f['file_size'])){
			$file_size = $f['file_size'];
		} else {
			//File size is not stored in entity, will need to be calculated
			//manually. Note that we can't simply call PHP filesize function
			//since the encrypted file has padding, may be up to 16 bytes
			//smaller than size reported by filesize
			$fn = $this->generateUploadFileName($f);
			$decryptor = $this->_make_file_encryptor($f);
			$file_size = $decryptor->decrypted_filesize($fn);
		}
		
		$this->echoUploadedFile($f, $file_name, $file_size, $content_type);
	}
	
	public function setEntityAttachmentPrivacy(Entity $entity, $new_private=true, $options=[]){
		//check that the entity privacy can be changed
		if ($entity->allowsPrivacyChange()){
			$f = $entity->getAttachmentDescription();
			$new_f = $this->changeUploadedFilePrivacy($f, $new_private);
			//$new_f is almost certainly the same as $f, but maybe the key or iv have changed
			//in this very rare case, we may need to modify the entity
			$entity->attachFile($new_f);
		} else {
			//throw an error
		}
		
		return $entity;
	}
}
?>
