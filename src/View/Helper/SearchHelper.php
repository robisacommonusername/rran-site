<?php
namespace App\View\Helper;

use Cake\View\Helper;

class SearchHelper extends Helper
{
	public $helpers = ['Form'];
	
	public function searchBox($options=null){
		if (!isset($options) || is_null($options)){
			$options = array();
		}
		//set up default options
		if (!isset($options['type'])){
			$options['type'] = 'get';
		}
		if (!isset($options['action'])){
			$options['action'] = 'index';
		}
		if (!isset($options['label'])){
			$options['label'] = 'Search:';
		}
		if (!isset($options['button_label'])){
			$options['button_label'] = 'Search';
		}
		if (!isset($options['input_name'])){
			$options['input_name'] = 'query';
		}
		
		//build the form
		$ret = '<center>'.$this->Form->create(null,$options);
		$ret = $ret . '<fieldset>';
		$ret = $ret . $this->Form->label($options['input_name'],$options['label']);
		$ret = $ret . $this->Form->text($options['input_name']);
		$ret = $ret . $this->Form->button(__($options['button_label']));
		$ret = $ret . $this->Form->end();
		$ret = $ret . '</fieldset></center>';
		
		return $ret;
	}

}
?>
