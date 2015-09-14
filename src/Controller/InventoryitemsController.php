<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Inventoryitems Controller
 *
 * @property \App\Model\Table\InventoryitemsTable $Inventoryitems
 */
class InventoryitemsController extends AppController
{
	
	//Set up authorization handler
	public function isAuthorized($user){
		//everyone can do everything
		return true;
	}

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
		$this->paginate = [
            'contain' => ['Users']
        ];
		if (isset($_GET['query'])){
			$str = '%'.$_GET['query'].'%';
			$query = $this->Inventoryitems->find('all', ['contain'=>['Users']])
				->where(['description like' => $str]);//get the relevant minutes
			$items = $this->paginate($query);
		} else {
			$items = $this->paginate($this->Inventoryitems);
		}
        
        $this->set('inventoryitems', $items);
        $this->set('_serialize', ['inventoryitems']);
    }

    /**
     * View method
     *
     * @param string|null $id Inventoryitem id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $inventoryitem = $this->Inventoryitems->get($id, [
            'contain' => ['Users']
        ]);
        $this->set('inventoryitem', $inventoryitem);
        $this->set('_serialize', ['inventoryitem']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $inventoryitem = $this->Inventoryitems->newEntity();
        if ($this->request->is('post')) {
            $inventoryitem = $this->Inventoryitems->patchEntity($inventoryitem, $this->request->data);
            if ($this->Inventoryitems->save($inventoryitem)) {
                $this->Flash->success(__('The inventoryitem has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The inventoryitem could not be saved. Please, try again.'));
            }
        }
        $users = $this->Inventoryitems->Users->find('all', ['limit' => 200]);
        //form a mapping from the user ids to a name or title
        $usermap = $this->mapUsers($users);
        $this->set(compact('inventoryitem', 'usermap'));
        $this->set('_serialize', ['inventoryitem']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Inventoryitem id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $inventoryitem = $this->Inventoryitems->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $inventoryitem = $this->Inventoryitems->patchEntity($inventoryitem, $this->request->data);
            if ($this->Inventoryitems->save($inventoryitem)) {
                $this->Flash->success(__('The inventoryitem has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The inventoryitem could not be saved. Please, try again.'));
            }
        }
        $users = $this->Inventoryitems->Users->find('all', ['limit' => 200]);
        $usermap = $this->mapUsers($users);
        $this->set(compact('inventoryitem', 'usermap'));
        $this->set('_serialize', ['inventoryitem']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Inventoryitem id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $inventoryitem = $this->Inventoryitems->get($id);
        if ($this->Inventoryitems->delete($inventoryitem)) {
            $this->Flash->success(__('The inventoryitem has been deleted.'));
        } else {
            $this->Flash->error(__('The inventoryitem could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
    
    protected function mapUsers($users){
		//create map from id to a username
		$map = array();
		foreach ($users as $user){
			$map[$user['id']] = $user->displayName();
		}
		return $map;
	}
}
