<?php
namespace App\Model\Behavior;

use Cake\ORM\Behavior;

class UploadableBehavior extends Behavior{
	public $_defaultConfig = [
		'fields' => [
			'file_name',
			'file_size',
			'content_type',
			'key'
		],
		'private' => true,
		'encrypted' => false
	];
	
	public $upload_private = true;
	public $upload_encrypted = false;
	public $upload_fields = [];
	
	public function initialize(array $config){
		parent::initialize($config);
		
		if (array_key_exists('private', $config)) $this->upload_private = $config['private'];
		if (array_key_exists('encrypted', $config)) $this->upload_encrypted = $config['encrypted'];
		if (array_key_exists('fields', $config)) $this->upload_fields = $config['fields'];
	}
	
	public function field_map(){
		//Create field map of the form canonical_name => translated_name
		//$this->fields may have a combination of numeric and string indices.
		//Numerical index indicates no over-riding of the name, i.e.
		//canonical_name == translated_name
		//If there is a redefinition of the field name under both a string
		//and numerical index, the value indexed by the string will take precedence.
		//If a field is defined multiple times under a numerical index, the
		//last one suplpied will take precedence (i.e. the one with the highest index)
		//Any data in $this->fields with a non numeric and non-string index 
		//will be discarded.
		
		$map = [];
		
		$indices = array_keys($this->upload_fields);
		$numeric_indices = array_filter($indices, 'is_numeric');
		$string_indices = array_filter($indices, 'is_string');
		
		foreach ($numeric_indices as $i){
			$name = $this->upload_fields[$i];
			$map[$name] = $name;
		}
		
		foreach ($string_indices as $canonical_name){
			$translated_name = $this->upload_fields[$canonical_name];
			$map[$canonical_name] = $translated_name;
		}
		
		return $map;
	}
	
	public function upload_encrypted(){
		return $this->upload_encrypted;
	}
	
	public function upload_private(){
		return $this->upload_private;
	}
	
	
}
?>
