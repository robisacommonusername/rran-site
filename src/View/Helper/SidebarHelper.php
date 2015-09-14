<?php
namespace App\View\Helper;

use Cake\View\Helper;

class SidebarHelper extends Helper
{
	public $helpers = ['Html'];
	
    public function placeLinks($exceptions, $userData){
		$links = '<ul class="side-nav">';
		if (!in_array('/', $exceptions)){
			$links = $links . '<li>'.$this->Html->link('Home',['controller' => 'Home', 'action'=>'index']).'</li>';
		}
		if (!in_array('minutes/', $exceptions)) {
			$links = $links . '<li>'. $this->Html->link('Meeting Minutes', ['controller' => 'Minutes', 'action' => 'index']) . '</li>';
		}
		if (!in_array('uploadedfiles/', $exceptions)){
			 $links = $links . '<li>' . $this->Html->link('Uploaded files (shared drive)', ['controller' => 'Uploadedfiles', 'action' => 'index']) . '</li>';
		}
		if (!in_array('inventoryitems/', $exceptions)){
			$links = $links . '<li>' . $this->Html->link('Inventory', ['controller' => 'Inventoryitems', 'action' => 'index']) . '</li>';
		}
        if (!in_array('users/', $exceptions)){
			if ($userData['is_admin']){
				$links = $links.'<li>'.$this->Html->link('Manage users', ['controller' => 'Users', 'action' => 'index']).'</li>';
			}
		}
		if (!in_array('users/edit/:id', $exceptions)){
			$links = $links.'<li>'.$this->Html->link('Edit your contact information', ['controller' => 'Users', 'action' => 'edit', $userData['id']]).'</li>';
		}
		$links = $links.'</ul>';

		return $links;
    }


}
?>
