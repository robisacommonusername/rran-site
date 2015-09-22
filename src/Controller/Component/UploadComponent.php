<?php
namespace App\Controller\Component;
require('FileEncryptor.php');
use Cake\Controller\Component;

class UploadComponent extends Component {
	
	private function _get_encryption($f){
		$encrypt = false;
		if (isset($f['encrypt'])) $encrypt = $f['encrypt'];
		if (!isset($f['iv'])) $encrypt = false;
		
		return $encrypt;
	}
	
	private function _make_file_encryptor($f){
		$encrypt = $this->_get_encryption($f);
		$key = \FileEncryptor::destringify_key($f['key'])
		if ($encrypt){
			$iv = \FileEncryptor::destringify_key($f['iv']);
			$encryptor = new \FileEncryptor($key, $iv);
		} else {
			$encryptor = new \FileEncryptor($key);
		}
		
		return $encryptor;
	}
	
	public function generateUploadFileName($f){
		/*
		 * $f = ['key' => key (hex format)
		 * 	'encrypt' =>
		 *  'private' =>
		 *  'iv' =>]
		 */
		$private = isset($f['private']) ? $f['private'] : true;
		
		$encrypt = $this->_get_encryption($f);
		
		$hex_key = $encrypt ? $f['iv'] : $f['key'];
		
		$path = $private ? 'uploads/private/' : 'uploads/';
		$path = WWW_ROOT . $path;
		$fn = "$path$hex_key.aes";
		return $fn;
	}
	
	public function uploadedFileExists($f){
		$fn = $this->generateUploadFileName($f);
		return file_exists($fn);
	}
	
	public function escapeHttpFilename($fn){
		//generate a "nice" filename which is safe for inclusion in
		//a http header - no specials allowed
		if (preg_match('/^[a-zA-Z0-9_\\- .]+/', $fn)){
			$nice_fn = $fn;
		} else {
			$nice_fn = 'unnamed_file';
		}
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
		
		//check for PHP error flag
		if ($f['error'] !== UPLOAD_ERR_OK){
			$success = false;
			switch ($f['error']){
				case \UPLOAD_ERR_NO_FILE:
				$message = 'No file was uploaded';
				break;
				
				case \UPLOAD_ERR_INI_SIZE:
				case \UPLOAD_ERR_FORM_SIZE:
				$message = 'Size of uploaded file exceeds max file size set on server';
				break;
				
				default:
				$message = 'Unknown error. PHP was unable to process upload';
			}
			
			return array('success' => $success, 'message' => $message); 
		}
		
		//check upload size, ensure it is not too big. Allow up to 30MB
		if ($f['size'] > 30*1024*1024){
			return array('success' => false, 
				'message' => 'Uploaded files may not exceed 30MB');
		}
		
		//generate random file name for encrypted file, which will also
		//be the encryption key
		$iv = call_user_func_array('pack', array_pad(['C*'], 17, 0)); //default is all 0
		$up = array('private' => $private, 'encrypt' => $encrypt);
		do {
			$key = \FileEncryptor::generate_key();
			$up['key'] = \FileEncryptor::stringify_key($key);
			if ($encrypt) {
				$iv = \FileEncryptor::generate_iv();
				$up['iv'] = \FileEncryptor::stringify_key($iv);
			}
			$fn = $this->generateUploadFileName($up);
		} while (file_exists($fn));
		
		//encrypt and upload the file. Note that we use a blank IV,
		//since the encryption isn't for the purpose of keeping the
		//contents secret (it's to render the file safe)
		$encryptor = new \FileEncryptor($key,$iv);
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
		
		$ret['iv'] = \FileEncryptor::stringify_key($iv);
		
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
				$f_handle = fopen($old_name,'r')
				$decryptor->decrypt($f_handle, $tmp_handle);
				fclose($f_handle);
				
				//generate new name
				do {
					if ($encrypt) {
						$f['iv'] = \FileEncryptor::stringify_key(\FileEncryptor::generate_iv());
					} else {
						$f['key'] = \FileEncryptor::stringify_key(\FileEncryptor::generate_key());
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
			
			return $f;
			
		}
		return false;
	}
	
	private function _process_options($options){
		//options:
		/*
		 * [
		 * private => true,
		 * encrypt => false,
		 * fields => ['file_name', 'file_size',
		 * 		'file_type' => 'mime_type', //translation
		 * 		] //content_key and iv will always be included depending on encrypt setting, but can be translated
		 * ]
		 * 		
		 */
		 //Get the options
		 $private = array_key_exists($options,'private') ? $options['private'] : true;
		 $encrypt = array_key_exists($options,'encrypt') ? $options['encrypt'] : false;
		 if (array_key_exists($options, $fields)){
			 $fields = array();
			 //convert the numeric indices
			 foreach ($options['fields') as $k => $v){
				 if (is_numeric($k)){
					 $fields[$v] = $v;
				 } else {
					 $fields[$k] = $v;
				 }
			 }
			 if (!array_key_exists($fields, 'key')) $fields['key'] = 'key';
			 if ($encrypt && !array_key_exists($fields,'iv')) $fields['iv'] = 'iv';

		 } else {
			$fields = array('file_name' => 'file_name', 'file_size' => 'file_size',
				'content_type' => 'content_type', 'key' => 'key', 'private' => 'private');
			if ($encrypt) $fields['iv'] = 'iv';
			
		 }
		 
		 return array($fields, $private, $encrypt);
	}
	
	public function attachToEntity($entity, $file, $options=[]){
		
		//Get the upload options
		 list($fields, $private, $encrypt) = $this->_process_options($options);
		 
		 //Upload the file
		 $ret = $this->uploadFile($file, $private);
		 
		 //set the file information on the entity
		 if ($ret['success']){
			$canonical_names = array('file_name', 'file_size', 
				'content_type', 'key');
			if ($encrypt) $canonical_names[] = 'iv';
			foreach ($canonical_names as $f){
				$entity[$fields[$f]] = $ret[$f];
			}
				
		 }
		 
		 return $ret['success'];
	}
	
	private function _get_file_description_from_entity($entity, $options=[]){
		list($fields) = $this->_process_options($options);
		
		//Determine if file is encrypted
		if (!isset($fields['iv']
			|| !isset($entity[$fields['iv']]) 
			|| $entity[$fields['iv']] === null){
				$encrypt = false;
		} else {
			$encrypt = true;
		}
		
		//determine if file is private
		$private = $entity[$fields['private']];
		$key = $entity[$fields['key']];
		
		$f = array('private' => $private, 'key' => $key, 'encrypt' => $encrypt);
		if ($encrypt){
			$iv = $entity[$fields['iv']];
			$f['iv'] = $iv;
		}
		
		return $f;
	}
	
	public function detachFromEntity($entity, $options){
		
		$f = $this->_get_file_description_from_entity($entity, $options=[]);
		
		return $this->deleteUploadedFile($f);
	}
	
	public function downloadFromEntity($entity, $options=[]){
		//get the attached file from the entity, and send it to the browser
		$f = $this->_get_file_description_from_entity($entity, $options);
		list($fields) = $this->_process_options($options);
		$file_name = isset($fields['file_name']) ? $entity[$fields['file_name']] : 'unnamedfile';
		$content_type = isset($fields['content_type']) ? $entity[$fields['content_type']] : 'application/octet-stream';
		if (isset($fields['file_size'])){
			$file_size = $entity[$fields['file_size']];
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
}
?>
