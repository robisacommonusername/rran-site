<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Minutes Controller
 *
 * @property \App\Model\Table\MinutesTable $Minutes
 */
class HomeController extends AppController{


	public function initialize(){
		parent::initialize();
		$this->loadModel('Minutes');
		$this->loadModel('Inventoryitems');
		$this->loadModel('Uploadedfiles');
		$this->loadModel('Users');
		$this->loadModel('Tags');
	}
	
	public function isAuthorized($user){
		return true;
	}
	
    /**
     * Index method
     *
     * @return void
     */
    public function index(){
		$now = time();
		$last = $now - 14*24*60*60; //last fortnight
		$d = date('Y-m-d H:m:i', $last);
		$minutes = $this->Minutes->find()
			->where(['minutes.updated >' => $d])
			->order(['minutes.meeting_date' => 'desc']);
		$items = $this->Inventoryitems
			->find('all', ['contain' => ['Users']])
			->where(['inventoryitems.updated >' => $d]);
		$files = $this->Uploadedfiles->find('all', ['contain' => ['Tags']])
			->where(['uploadedfiles.updated >' => $d])
			->order(['uploadedfiles.updated' => 'desc']);
		$users = $this->Users->find()
			->where(['users.created >' => $d]);
		$this->set(compact('minutes','items','users','files'));
	}
}
?>
