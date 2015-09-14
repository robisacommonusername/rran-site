<?php

require_once('AES.php');
//Class to encrypt a file chunk by chunk, rather than by reading entire
//file into memory. 
//
//Uses AES-128 CBC, because even though CTR mode would
//be better, what with the hassles of padding in CBC, CTR is undocumented
//(though it is implemented) in PHP mcrypt extension, and I'm not comfortable
//relying on undocumented modes. The use of CBC creates slight computational overhead,
//generally 1 extra AES function computation per 8k of data for encryption,
//and 2 extra AES computations per 8k for decryption. Overhead can be decreased
//by increasing "chunk" variable, at cost of more memory.
//
//specify $chunk in terms of AES blocks (multiples of 16 bytes). 8k = 512 blocks
class FileEncryptor{
	
	public function __construct($key,$iv=null,$chunk=512){
		$this->key = $key;
		if (is_null($iv) || (!is_string($iv)) || strlen($iv) != 16){
			//default IV is all 0
			//PHP is stupid, and pack doesn't work on arrays, necessitating idiocy
			$this->iv = call_user_func_array('pack', array_pad(['C*'], 17, 0));
		} else {
			$this->iv = $iv;
		}
		//chunk should be specified in number of AES blocks (i.e. 16 bytes per block).
		//$this->chunk is stored internally in bytes
		$this->chunk = $chunk*16;
		$this->next_iv = $this->iv;
	}
	
	public static function generate_key(){
		//generate a random aes key securely
		//makes a 128 bit key, returned as a string of 16 raw bytes
		$bytes = null;
		//try to get something cryptographically secure (*nix only)
		if (file_exists('/dev/urandom')){
			$f = fopen('/dev/urandom', 'r');
			$bytes = fread($f, 16);
			fclose($f);
			//if any of these fail, PHP will throw warning, $bytes will be false
		}
		if ($bytes === false || $bytes === null) {
			//fallback using mersenne twister.  Not great, but hopefully can extract
			//enough entropy from mt_rand without being able to reconstruct internal state.
			//THE HASHING IS IMPORTANT! Must not give attacker access to the outputs!
			//further note - must NOT use mt_rand ANYWHERE in code where it can give
			//attacker access to output.  Should probably enforce this somehow.
			// Want 128 bits
			for ($i=0; $i<6; $i++){
				$bytes = hash('sha256', $bytes . mt_rand(), True);
			}
		}
		return substr($bytes,0,16);
	}
	
	public static function stringify_key($key){
		//return binary string $key (16 bytes) as hex encoded string (32 characters)
		$s = unpack('H*', $key);
		return substr($s[1], 0, 32);
	}
	
	public static function destringify_key($hex_key){
		$nbytes = strlen($hex_key)/2;
		$out = '';
		for ($i=0; $i < $nbytes; $i++){
			$out = $out . chr(hexdec(substr($hex_key, 2*$i, 2)));
		}
		return $out;
	}
	
	private function _encrypt_chunk($plain_text, $last=false){
		$aes = new Crypt_AES();
		$aes->setKey($this->key);
		$aes->setIV($this->next_iv);
		$cipher_text = $aes->encrypt($plain_text);
		//Plain text will have length that is a multiple of 16 bytes,
		//unless it is the last chunk. Therefore the padding block will
		//be 16 bytes added to the end of the cipher text, which we need
		//to remove, unless this is the last chunk
		if (!$last){
			$cipher_text = substr($cipher_text,0,-16);
		}
		//set the next iv equal to the last cipher text block
		$this->next_iv = substr($cipher_text, -16);
		
		return $cipher_text;
	}
	
	private function _decrypt_chunk($cipher_text, $last=false){
		$aes = new Crypt_AES();
		$aes->setKey($this->key);
		$aes->setIV($this->next_iv);
		//if this is not the last chunk, we will need to reconstruct the
		//encrypted padding block
		if (!$last){
			$dummy_iv = substr($cipher_text, -16);
			$dummy_plain = call_user_func_array('pack',array_pad(['C*'],17,16));
			$dummy_aes = new Crypt_AES();
			$dummy_aes->setKey($this->key);
			$dummy_aes->setIV($dummy_iv);
			$dummy_cipher = $dummy_aes->encrypt($dummy_plain);
			//$dummy_cipher itself has a padding block that we need to remove
			$dummy_block = substr($dummy_cipher, 0, 16);
			$cipher_text = $cipher_text . $dummy_block;
			
			$this->next_iv = $dummy_iv;
		}
		
		$plain_text = $aes->decrypt($cipher_text);
		
		return $plain_text;
	}
	
	//$fp_in and out are file pointers, must have correct read/write permission
	public function encrypt($fp_in, $fp_out){
		if (is_null($fp_in) || is_null($fp_out)){
			return false;
		}
		
		//initialize the chunk iv
		$this->next_iv = $this->iv;
		
		//read in chunk by chunk
		while (!feof($fp_in)){
			$chunk = fread($fp_in, $this->chunk);
			$last = feof($fp_in);
			$cipher_chunk = $this->_encrypt_chunk($chunk, $last);
			fwrite($fp_out, $cipher_chunk);
		}
		
		return true;
	}
	
	public function decrypt($fp_in, $fp_out){
		if (is_null($fp_in) || is_null($fp_out)){
			return false;
		}
		
		//initialize the chunk iv
		$this->next_iv = $this->iv;
		
		while (!feof($fp_in)){
			$cipher_chunk = fread($fp_in, $this->chunk);
			$last = feof($fp_in);
			$plain_chunk = $this->_decrypt_chunk($cipher_chunk, $last);
			fwrite($fp_out, $plain_chunk);
		}
		
		return true;
	}
	
	public function decrypt_to_browser($fp_in){
		if (is_null($fp_in)){
			return false;
		}
		
		//initialize the chunk iv
		$this->next_iv = $this->iv;
		
		//disable script timeout
		set_time_limit(0);
		
		//decrypt each chunk, send to browser, flush output buffers
		while (!feof($fp_in)){
			$cipher_chunk = fread($fp_in, $this->chunk);
			$last = feof($fp_in);
			$plain_chunk = $this->_decrypt_chunk($cipher_chunk, $last);
			
			echo $plain_chunk;
			
			ob_flush();
			flush();
		}
		return true;
	}
	
	public function encrypt_file_to_file($fn_in, $fn_out){
		list($fp_in, $fp_out) = FileEncryptor::get_file_pointers(
			array($fn_in, $fn_out), array('r','w'));
			
		$ret = $this->encrypt($fp_in, $fp_out);
		
		fclose($fp_in);
		fclose($fp_out);
		
		return $ret;
	}
	
	public function decrypt_file_to_file($fn_in, $fn_out){
		list($fp_in, $fp_out) = FileEncryptor::get_file_pointers(
			array($fn_in, $fn_out), array('r','w'));
		
		$ret = $this->decrypt($fp_in, $fp_out);
		
		fclose($fp_in);
		fclose($fp_out);
		
		return $ret;
	}
	
	public function decrypt_file_to_browser($fn_in){
		list($fp_in) = FileEncryptor::get_file_pointers($fn_in, 'r');
		
		$ret = $this->decrypt_to_browser($fp_in);
		
		fclose($fp_in);
		
		return $ret;
	}
	
	public static function get_file_pointers($fns, $modes){
		if (is_array($fns)){
			//if we have an array of filenames, make sure we have an array of modes
			if (!is_array($modes)){
				$modes = array_fill(0,count($fns),$modes);
			}
			
			//make sure arrays are same length
			$num_fns = count($fns);
			$num_modes = count($modes);
			if ($num_fns > $num_modes){
				$last = $modes[$num_modes - 1];
				$modes = array_pad($modes, $num_fns, $last);
			}
		} else {
			$fns = array($fns);
			$modes = array(is_array($modes) ? $modes[0] : $modes);
		}
		
		$fn_modes = array_combine($fns, $modes);
		
		$out_fps = array();
		
		foreach ($fn_modes as $fn => $mode){
			if (preg_match('/^r/', $mode)){
				//for read modes, file must exist
				if (!file_exists($fn)){
					continue;
				}
			}
			//attempt to open the file
			$f = fopen($fn, $mode);
			//php returns "FALSE" on failure to open stream, because it
			//is such a "wonderfully" "designed" "language".
			if ($f !== false){
				$out_fps[] = $f;
			}
		}
		
		return $out_fps;
	}
}
?>
