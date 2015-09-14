<?php
namespace App\View\Helper;

use Cake\View\Helper;

class TagHelper extends Helper
{
	public $helpers = ['Html'];
	
    public function tagLinks($tags){
		$links = '<ul>';
        foreach ($tags as $tag) {
			$links = $links . '<li><span class="tagspan">' . $this->Html->link(h($tag['label']), 
				['controller' => 'Tags', 'action' => 'view', $tag['id']]) . '</span></li>';
		}
		$links = $links . '</ul>';
		return $links;
    }
    
    public function assosciatedFiles($uploadedfiles){
		//map file id to file name
		$map = array();
		foreach ($uploadedfiles as $f){
			$map[$f['id']] = $f['file_name'];
		}
		return $map;
	}
}
?>
