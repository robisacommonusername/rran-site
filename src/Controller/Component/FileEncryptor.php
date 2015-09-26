<?php
//Class to encrypt a file chunk by chunk, rather than by reading entire
//file into memory. 
//
//Uses AES-128 CBC, because even though CTR mode would
//be better, what with the hassles of padding in CBC, CTR is undocumented
//(though it is implemented) in PHP mcrypt extension, and I'm not comfortable
//relying on undocumented modes. The use of CBC creates slight computational overhead,
//generally 1 extra AES function computation per 8k of data for encryption
//and two extra AES evaluations for decryption.
// Overhead can be decreased by increasing "chunk" variable, at cost of more memory.
//
//specify $chunk in terms of AES blocks (multiples of 16 bytes). 8k = 512 blocks
//
//Can optionally compute a MAC of the ENCRYPTED data using AES-CMAC (RFC 4493)
//using a separate key.
//See https://tools.ietf.org/html/rfc4493 for details
//
// (C) Robert Palmer, 2015
namespace App\Controller\Component;

require_once('AES.php');

class FileEncryptor{
	
	private $compute_mac = false;
	public $tag = null;
	private $mac_key = null;
	
	public function __construct($key,$iv=null,$chunk=512){
		$this->key = $key;
		if (is_null($iv) || (!is_string($iv)) || strlen($iv) != 16){
			//default IV is all 0
			$this->iv = FileEncryptor::pack('C*', array_pad(array(), 16, 0));	
		} else {
			$this->iv = $iv;
		}
		
		//chunk should be specified in number of AES blocks (i.e. 16 bytes per block).
		//$this->chunk is stored internally in bytes
		$this->chunk = $chunk*16;
		$this->reset();
	}
	
	public function reset(){
		$this->next_iv = $this->iv;
		//empty IV (0^128)
		$this->tag = FileEncryptor::pack('C*', array_pad(array(), 16, 0));
	}
	
	public function set_key($key){
		if (!is_null($key) && is_string($key) && strlen($key) === 16) {
			$this->key = $key;
		} else {
			throw new Exception('Bad key supplied. Keys for AES-128-CBC should be 16 bytes');
		}
	}
	
	public function set_iv($iv){
		if (!is_null($iv) && is_string($iv) && strlen($iv) === 16) {
			$this->iv = $iv;
		} else {
			throw new Exception('Bad IV supplied. IV for AES-CBC should be 16 bytes');
		}
	}
	
	public function enable_MAC($key){
		$this->compute_mac = true;
		//Check the supplied key
		if (!is_null($key) && is_string($key) && strlen($key) === 16) {
			$this->mac_key = $key;
		} else {
			throw new Exception('Bad MAC key supplied. Keys for AES-CMAC should be 16 bytes');
		}
		//Check that user hasn't supplied same keys for both encrypt and mac
		if ($this->mac_key === $this->key){
			trigger_error('You should not use the same key for both encryption and authentication', E_USER_WARNING);
		}
	}
	
	public function disable_MAC(){
		$this->compute_mac = false;
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
			if ($bytes !== false && $bytes !== null){
				return $bytes;
			}
		}
		
		//First fallback - openssl
		if (function_exists('openssl_random_pseudo_bytes')){
			$bytes = openssl_random_pseudo_bytes(16, $is_strong);
			if ($bytes !== null && $bytes !== false && $is_strong){
				return $bytes;
			}
		}
		
		//Second fallback. See if mcrypt_create_iv function is available
		if (function_exists('mcrypt_create_iv')){
			$bytes = mcrypt_create_iv(16); //returns false on failure
			if ($bytes !== false && $bytes !== null){
				return $bytes;
			}
		}
		
		//Final fallback. This is not good, raise a warning!
		//fallback using mersenne twister.  Not great, but hopefully can extract
		//enough entropy from mt_rand without being able to reconstruct internal state.
		//THE HASHING IS IMPORTANT! Must not give attacker access to the outputs!
		//further note - must NOT use mt_rand ANYWHERE in code where it can give
		//attacker access to output. This method is safe enough if the
		//user of the library never discloses any outputs of mt_rand.
		// Want 128 bits
		trigger_error("Falling back to hashed Mersenne-twister for key generation. You must ensure that no outputs of mt_rand() are disclosed to the user anywhere in your code. The preferred solution would be to install the mcrypt or openssl extensions", E_USER_WARNING);
		for ($i=0; $i<6; $i++){
			$bytes = hash('sha256', $bytes . mt_rand(), True);
		}
		
		return substr($bytes,0,16);
	}
	
	public static function generate_iv(){
		//In our case, since we're using AES-128, the IV and key have 
		//the same size, but in general we will need separate function
		//for generating IVs and keys
		return FileEncryptor::generate_key();
	}
	
	public static function stringify_key($key){
		//return binary string $key (16 bytes) as hex encoded string (32 characters)
		$s = unpack('H*', $key);
		return $s[1];
	}
	
	public static function destringify_key($hex_key){
		$nbytes = strlen($hex_key)/2;
		$out = '';
		for ($i=0; $i < $nbytes; $i++){
			$out = $out . chr(hexdec(substr($hex_key, 2*$i, 2)));
		}
		return $out;
	}
	
	public static function pack($format, $data){
		//essentially the same as PHP built in pack function, except
		//you can call it on an array, $data
		return call_user_func_array('pack', array_merge(array($format), $data));
	}
	
	private function _xor($a,$b){
		//$a and $b should be equal length byte arrays
		//
		//Improve backward compatability by not using anon function
		//implementation. Anon function introduced PHP 5.3
		//
		//return array_map(function($aa, $bb){
		//	return $aa ^ $bb;
		//}, $a, $b);
		$ret = array();
		$n = min(array(count($a), count($b)));
		for ($i=0; $i<$n; $i++){
			$ret[] = $a[$i] ^ $b[$i];
		}
		return $ret;
	}
	
	private function _shift_left($bytes){
		//Be careful to avoid branching on carry, we don't want timing attacks!
		$carry = 0x00;
		$ret = array();
		foreach ($bytes as $b){
			$next = ($b << 1) ^ $carry;
			$carry = ($next & (1<<8)) >> 8;
			$next = $next & 0xff;
			$ret[] = $next;
		}
		return $ret;
	}
	
	private function _tweak_final_block($cipher_text){
		//Tweak the final block for use in AES-CMAC
		//
		//Derive the keys k1 and k2 from $this->key
		//See https://tools.ietf.org/html/rfc4493
		//Because we only ever use this MAC on AES-CBC ciphertexts,
		//the input size should be a multiple of the block size (16 bytes)
		//We still have to compute k2 of course, since an attacker could modify length
		//of the ciphertext.
		
		//Therefore, check the length of the ciphertext
		//Note that we don't do dummy block padding here, so if message
		//length is multiple of 16 bytes, pad length is zero.
		//Padding is the ISO format 1{0}^i
		$pad_len = (16 - (strlen($cipher_text) % 16)) % 16;
		$full_pad = FileEncryptor::pack('C*', array_pad(array(0x80), 16, 0x00));
		$pad = substr($full_pad, 0, $pad_len);
		$cipher_text = $cipher_text . $pad;
		
		//Set up some constants for k1, k2 calculation
		$zero_bytes = array_pad(array(), 16, 0);
		$zero = FileEncryptor::pack('C*', $zero_bytes);
		$aes = new \Crypt_AES( CRYPT_AES_MODE_ECB );
		$aes->setKey($this->mac_key);
		$aes->setIV($zero);
		$Ek_0 = substr($aes->encrypt($zero), 0, 16); //Have to remove dummy block
		$rb = array_pad(array(0x87), 16, 0x00);
		$Ek_0_bytes = array_reverse(unpack('C16',$Ek_0)); //Assume data is big-endian, work in little-endian internally
		//Note that unpack returns 1 indexed array because it is stupid,
		//But array reverse give us a zero indexed array again. Thanks PHP.
		
		//Generate k1
		$msb_Ek_0 = $Ek_0_bytes[15] & (1<<7);
		$Ek_0_bytes_sl = $this->_shift_left($Ek_0_bytes);
		//if msb, xor Ek_0 << 1 with rb
		if ($msb_Ek_0){
			$k1_bytes = $this->_xor($Ek_0_bytes_sl, $rb);		
		} else {
			$k1_bytes = $this->_xor($Ek_0_bytes_sl, $zero_bytes); //no timing attack, xor with zero
		}

		//generate k2
		$msb_k1 = $k1_bytes[15] & (1<<7);
		$k1_bytes_sl = $this->_shift_left($k1_bytes);
		if ($msb_k1){
			$k2_bytes = $this->_xor($k1_bytes_sl, $rb);
		} else {
			$k2_bytes = $this->_xor($k1_bytes_sl, $zero_bytes); //no timing attack, xor with zero
		}
		
		//xor the last ciphertext block with the key k1 (most likely) or
		//k2 if the $pad_len was non multiple of 16 bytes (i.e. tampered with)
		$last_block = substr($cipher_text, -16);
		$rest = substr($cipher_text,0,-16);
		$last_block_bytes = array_reverse(unpack('C16', $last_block));
		$tweak_key = $pad_len > 0 ? $k2_bytes : $k1_bytes;
		$last_tweaked_bytes = $this->_xor($last_block_bytes, $tweak_key);
		$last_tweaked = FileEncryptor::pack('C*', array_reverse($last_tweaked_bytes));
		
		$tweaked = $rest . $last_tweaked;
		
		return $tweaked;
	}
	
	private function _mac_chunk($cipher_text, $last=false){
		$aes = new \Crypt_AES();
		$aes->setKey($this->mac_key);
		$aes->setIV($this->tag);
		if (!$last){
			$mac_ct = $aes->encrypt($cipher_text);
		} else {
			//tweak the last block of ciphertext. Note that cipher_text_tweak
			//will always be a multiple of the block size
			$cipher_text_tweak = $this->_tweak_final_block($cipher_text);
			$mac_ct = $aes->encrypt($cipher_text);
		}
		$mac_ct = substr($mac_ct,0,-16); //remove dummy block - there will always be one
		$this->tag = substr($mac_ct, -16);
	}
	
	private function _encrypt_chunk($plain_text, $last=false){
		$aes = new \Crypt_AES();
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
		
		//Compute the AES-CMAC on the cipher-text if the option is enabled
		if ($this->compute_mac){
			$this->_mac_chunk($cipher_text, $last);
		}
		
		return $cipher_text;
	}
	
	private function _decrypt_chunk($cipher_text, $last=false){
		//Compute MAC on the ciphertext
		if ($this->compute_mac){
			$this->_mac_chunk($cipher_text, $last);
		}
		
		//Perform decryption
		$aes = new \Crypt_AES();
		$aes->setKey($this->key);
		$aes->setIV($this->next_iv);
		//if this is not the last chunk, we will need to reconstruct the
		//encrypted padding block
		if (!$last){
			$dummy_iv = substr($cipher_text, -16);
			$dummy_plain = FileEncryptor::pack('C*', array_pad(array(), 16, 16));
			$dummy_aes = new \Crypt_AES();
			$dummy_aes->setKey($this->key);
			$dummy_aes->setIV($dummy_iv);
			$dummy_cipher = $dummy_aes->encrypt($dummy_plain);
			//$dummy_cipher itself has a dummy block which needs to be removed
			$dummy_block = substr($dummy_cipher, 0, -16);
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
		
		//initialize the chunk iv and mac iv
		$this->reset();
		
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
		
		//initialize the chunk iv and cmac iv
		$this->reset();
		
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
		
		//initialize the chunk iv and cmac iv
		$this->reset();
		
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
	
	public function decrypted_filesize($fn){
		//get the size of the decrypted file in bytes
		$encrypted_size = filesize($fn);
		$f = fopen($fn, 'r');
		//check for the special case where the ciphertext is only one
		//block => message < 16 bytes
		if ($encrypted_size >= 32){
			//IV will be the second last ciphertext block
			fseek($f, $encrypted_size-32);
			$this->next_iv = fread($f,16);
		} else {
			$this->next_iv = $this->iv;
		}
		$last_ct_block = fread($f,16);
		$last_plaintext_block = $this->_decrypt_chunk($last_ct_block, true);
		//Note that because we called _decrypt_chunk with the "last" parameter
		//set to true, any padding on the block is stripped away. Thus the
		//amount of padding is 16 - length of last_plaintext_block.
		//Note that in the case where the last plaintext block was a dummy (pad)
		//block, _decrypt_chunk should return the empty string.
		$n_pad = 16 - strlen($last_plaintext_block);
		
		$plain_size = $encrypted_size - $n_pad;
		
		return $plain_size;
	}
}
?>
