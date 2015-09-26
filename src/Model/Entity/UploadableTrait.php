<?php
namespace App\Model\Entity;
use \Cake\ORM\TableRegistry;

trait UploadableTrait {
	
	
	private function _field_map(){
		//Get the table object
		$table = TableRegistry::get($this->source());
		return $table->field_map();
	}
	
	private function _is_private(){
		//Determine if this entity is a private upload. If there's a private
		//entry in the field map, read in the value from the entity.
		//If not, read in the default value from the table class
		$fields = $this->_field_map();
		if (isset($fields['private']) && $this->has($fields['private'])){
			$private = $this->get($fields['private']);
		} else {
			$table = TableRegistry::get($this->source());
			$private = $table->upload_private();
		}
		
		return $private;
	}
	
	private function _is_encrypted(){
		//Determine of this entity is encrypted. Note that to be encrypted
		//there must be a key and an iv. Initially, check if there is an
		//encrypted field in the entity, if there is, read in encrypted status,
		//then check for key, iv presence. If not, read in default from
		//table
		$fields = $this->_field_map();
		if (isset($fields['encrypted']) && $this->has($fields['encrypted'])){
			$encrypted = $this->get($fields['encrypted']);
		} else {
			$table = TableRegistry::get($this->source());
			$encrypted = $table->upload_encrypted();
		}
		
		if (!isset($fields['key']) || !$this->has($fields['key'])
			|| !isset($fields['iv']) || !$this->has($fields['iv'])){
				$encrypted = false;
		}
		
		return $encrypted;
	}
	
	public function getAttachmentDescription(){
		$fields = $this->_field_map();
		
		//determine private and encrypted status
		$encrypt = $this->_is_encrypted();
		$private = $this->_is_private();
		$f = ['encrypted' => $encrypt, 'private' => $private];
		
		//Get the file_name, file_size and content_type, key if available
		$extras = ['file_name', 'file_size', 'content_type', 'key'];
		foreach ($extras as $canonical_name){
			if (isset($fields[$canonical_name]) && $this->has($fields[$canonical_name])){
				$f[$canonical_name] = $this->get($fields[$canonical_name]);
			}
		}
		
		//if encrypted, add the IV
		if ($encrypt){
			$iv = $this->get($fields['iv']);
			$f['iv'] = $iv;
		}
		
		return $f;
	}
	
	public function attachFile($f){
		//set the file information on the entity
		$fields = $this->_field_map();
		foreach ($fields as $canonical_name => $translated_name){
			$this->set($translated_name, $f[$canonical_name]);
		}
	}
	
	public function allowsPrivacyChange(){
		//Return false if the privacy is fixed in the model, true
		//if there is a database field for recording the privacy
		$fields = $this->_field_map();
		
		$changeable = (isset($fields['private']) && $this->has($fields['private']));
		
		return $changeable;
	}
	
	
}
?>
