<?php
namespace App\View\Helper;

use Cake\View\Helper;
use App\Model\Entity\Uploadedfile;

class UploadedfileHelper extends Helper
{
	public $helpers = ['Html','Tag', 'Number', 'Form'];
	
	protected static $default_fields = [
			'file_name',
			'file_size',
			'is_private',
			'modified',
			'tags',
			'actions'
	];
	
	//Default Renderers
	public function file_name($f){
		$link = $this->Html->link(h($f->file_name),
			['controller' => 'Uploadedfiles',
			'action' => 'view',
			$f->id]
		);
		return $link;
	}
	
	public function file_size($f){
		return $this->Number->toReadableSize($f->file_size);
	}
	
	public function is_private($f){
		return $f->private ? 'Private' : 'Public';
	}
	
	public function tags($f){
		return $this->Tag->tagLinks($f->tags);
	}
	
	public function actions($f){
		//If the file is public, offer public link
		$links = [];
		if (!$f['private']){
			$links[] = $this->Html->link(__('Public link'),
				['controller' => 'Uploadedfiles',
				'action' => 'public_view',
				$f->id]
			);
		}
		$links[] = $this->Html->link(__('Edit'), 
			['controller' => 'Uploadedfiles',
			'action' => 'edit',
			$f->id]
		);
		$links[] = $this->Form->postLink(__('Delete'),
			['controller' => 'Uploadedfiles',
			'action' => 'delete',
			$f->id], 
			['confirm' => __('Are you sure you want to delete # {0}?', h($f->file_name))]
		);
		$content = implode(' ', $links);
		return $content;
	}
	
	
    public function renderRow(Uploadedfile $uploadedfile, array $field_opts=[]){
		//work out which fields to render. Field opts has form
		/* $field_opts = [
		 * 	'field1', 'field2',
		 * 	'field3' => function($f){
			 * //custom formatter
			 * return ...
		 * }
		 * ]
		 */
		 
		 
		 $fields = count($field_opts) > 0 ? [] : UploadedfileHelper::default_fields;
		 $formatters = [];
		 foreach ($field_opts as $k => $v){
			 if (is_numeric($k)){
				 $fields[] = $v;
			 } else {
				 $fields[] = $k;
				 $formatters[$k] = $v;
			 }
		 }
		
		//Build the table row
		$out = '<tr>';
		foreach ($fields as $field){
			$content = '';
			if (array_key_exists($field, $formatters)){
				$formatter = $formatters[$field];
				$content = $formatter($uploadedfile);
			} elseif (method_exists($this, $field === 'private' ? 'is_private' : $field)) {
				$content = $this->$field($uploadedfile);
			} else {
				if ($uploadedfile->has($field)){
					$content = h($uploadedfile->$field);
				}
			}
			$class = '"'. h($field) . '"';
			$out = $out . "<td class=$class>$content</td>";
		}
		$out = $out . '</tr>';
		
		return $out;
	}
}
?>
