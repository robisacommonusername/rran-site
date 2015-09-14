<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */ 
namespace App\Controller;
require('Component/FileEncryptor.php');
use Cake\Controller\Controller;
use Cake\I18n\Time;
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        Time::setToStringFormat('YYYY-MM-dd HH:mm:ss');
        $this->loadComponent('Csrf');
        $this->loadComponent('RequestHandler'); //for json data
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'authenticate' => [
                'Form' => [
                    'fields' => [
                        'username' => 'username',
                        'password' => 'password'
                    ]
                ]
            ],
            'loginAction' => [
                'controller' => 'Users',
                'action' => 'login'
            ]
        ]);
        
        // Allow the display action so our pages controller
        // continues to work. Allow any public controller actions
        $this->Auth->allow(['display']);
    }
    
    //allow us to get the user information in the view
    public function beforeRender(\Cake\Event\Event $event){
		 $this->set(['userData'=> $this->Auth->user()]);
	}
	
	//Authorization. By default, admin can do everything, everything else
	//is forbidden
	public function isAuthorized($user){
		if ($user['is_admin']){
			return true;
		}
		return false;
	}
    
    //The following methods are for handling file uploads in the application
    public function generateUploadFileName($hex_key, $private=true){
		$path = $private ? 'uploads/private/' : 'uploads/';
		$path = WWW_ROOT . $path;
		$fn = "$path$hex_key.aes";
		return $fn;
	}
	
	public function uploadedFileExists($hex_key, $private = true){
		$fn = $this->generateUploadFileName($hex_key, $private);
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
	
    public function uploadFile($f, $private=true){
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
		do {
			$key = \FileEncryptor::generate_key();
			$hex_key = \FileEncryptor::stringify_key($key);
			$fn = $this->generateUploadFileName($hex_key, $private);
		} while (file_exists($fn));
		
		//encrypt and upload the file. Note that we use a blank IV,
		//since the encryption isn't for the purpose of keeping the
		//contents secret (it's to render the file safe)
		$encryptor = new \FileEncryptor($key); //use default IV
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
		
		return array('success' => $success, 'message' => $message,
			'key' => $hex_key, 'name' => $fn, 'display_name' => $nice_fn, 
			'content_type' => $safe_content_type, 'file_size' => $f['size']);
	}
	
	
	public function echoUploadedFile($hex_key, $file_name, $file_size, $file_type, $private=true){
		//send an uploaded file to the browser, including all the headers
		if (!$this->uploadedFileExists($hex_key, $private)){
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
		$fn = $this->generateUploadFileName($hex_key, $private);
		$key = \FileEncryptor::destringify_key($hex_key);
		$decryptor = new \FileEncryptor($key);
		$ret = $decryptor->decrypt_file_to_browser($fn);
		
		//Don't do any more processing
		exit;
	}
	
	public function decodeUploadedFile($hex_key, $out_fn, $private=true){
		$fn = $this->generateUploadFileName($hex_key, $private);
		$key = \FileEncryptor::destringify_key($hex_key);
		$decryptor = new \FileEncryptor($key);
		$ret = $decryptor->decrypt_file_to_file($fn, $out_fn);
		return $ret;
	}
	
	public function deleteUploadedFile($hex_key, $private=true){
		$fn = $this->generateUploadFileName($hex_key, $private);
		return unlink($fn);
	}
	
	public function changeUploadedFilePrivacy($hex_key, $old_private=false,$new_private=false){
		if ($old_private ^ $new_private){
			$old_name = $this->generateUploadFileName($hex_key, $old_private);
			$new_name = $this->generateUploadFileName($hex_key, $new_private);
			return rename($old_name, $new_name);
		}
		return false;
	}
}
