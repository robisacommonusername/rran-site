<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
	
	public function isAuthorized($user){
		//Everyone can list and view users
		if (in_array($this->request->action, ['index','view'])){
			return true;
		}
		
		//Users can edit and delete themselves
		if (in_array($this->request->action, ['edit','delete'])){
			$id = (int) $this->request->params['pass'][0];
			if ($id === $this->Auth->user('id')){
				return true;
			}
		}
		
		//fall through to default (admin can do everything, i.e. add and edit and delete
		return parent::isAuthorized($user);
	}

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set('users', $this->paginate($this->Users));
        $this->set('_serialize', ['users']);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['Inventoryitems']
        ]);
        $this->set('user', $user);
        $this->set('_serialize', ['user']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
			//We can't patch the password, need to check confirmation
			if ($this->request->data['password'] !== $this->request->data['password2']){
				$this->Flash->error('Passwords do not match!');
				return $this->redirect(['action' => 'add']);
			}
			//Can't patch the is_admin property, do it manually
			if ($this->Auth->user('is_admin')){
				$user['is_admin'] = (bool) $this->request->data['is_admin'];
			} else {
				$user['is_admin'] = false;
			}
			$user['password'] = $this->request->data['password'];
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
			//We can't patch the password, check manually
			$p1 = $this->request->data['password'];
			$p2 = $this->request->data['password2'];
			if (strlen($p1) > 0 && strlen($p2) > 0){
				if ($p1 === $p2) {
					$user['password'] = $p1;
				} else {
					$this->Flash->error('Passwords did not match! Password not changed.');
				}
			}
			//Allow changing the admin status if we are currently an admin
            if ($this->Auth->user('is_admin')){
				$user['is_admin'] = (bool) $this->request->data['is_admin'];
			}
			
			//patch all other attributes. Unsetting is required, because otherwise
			//patchEntity sets password to false, and save fails
			unset($this->request->data['password']);
			unset($this->request->data['is_admin']);	
            $user = $this->Users->patchEntity($user, $this->request->data);
            
			
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
    
    public function login() {
	    if ($this->request->is('post')) {
	        $user = $this->Auth->identify();
	        if ($user) {
	            $this->Auth->setUser($user);
	            return $this->redirect($this->Auth->redirectUrl());
	        }
	        $this->Flash->error('Your username or password is incorrect.');
	    }
	}
    
    public function logout() {
	    $this->Flash->success('You are now logged out.');
	    return $this->redirect($this->Auth->logout());
	}
}
