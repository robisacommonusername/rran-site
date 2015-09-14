<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Install Controller
 *
 * @property \App\Model\Table\InstallTable $Install
 */
class InstallController extends AppController
{

    /**
     * Index method
     *
     * @return void
     */
	
	public function initialize(){
		 parent::initialize();
		 $this->loadModel('Users');
		 $this->Auth->allow(['index', 'install']);
	 }
	 
    public function index()
    {
        //Don't display installer unless we haven't done an install yet
        if ($this->Users->find('all')->count() > 0){
			$this->Flash->error(__('Already installed. Please log in'));
			return $this->redirect('/');
		}
    }
    
    public function install(){
		//TODO - check users table is empty
		if ($this->Users->find('all')->count() > 0){
			$this->Flash->error(__('Already installed. Please log in'));
			return redirect('/');
		}
		if ($this->request->is('post')){
			$admin = $this->Users->newEntity();
			$admin['username'] = $this->request->data['username'];
			$admin['password'] = $this->request->data['password'];
			$admin['is_admin'] = true;
			if ($this->request->data['password'] === $this->request->data['password2']
				&& $this->Users->save($admin)){
					$this->Flash->success(__('Admin user successfully created. Please log in.'));
					return $this->redirect('/');
			} else {
				$this->Flash->error(__('Admin user could not be created. Check that passwords match.'));
				return $this->redirect(['action' => 'index']);
			}
		}
	}
}
