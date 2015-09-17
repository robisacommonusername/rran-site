<?php
namespace App\Controller;

use App\Controller\AppController;
//use Cake\Database\Log\QueryLogger;
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
			->where(['modified >' => $d])
			->order(['meeting_date' => 'desc']);
		//$logger = new QueryLogger();
		$items = $this->Inventoryitems
			->find('all')
			->where(['Inventoryitems.modified >' => $d]) //Note that mysql freaks out if we don't capitalise the table name here, but postgresql carries on fine
			->contain(['Users']);
		//die();
		$files = $this->Uploadedfiles->find('all', ['contain' => ['Tags']])
			->where(['Uploadedfiles.modified >' => $d])
			->order(['Uploadedfiles.modified' => 'desc']);
		$users = $this->Users->find()
			->where(['created >' => $d]);
		$this->set(compact('minutes','items','users','files'));
	}
}
?>
