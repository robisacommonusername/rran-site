<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Tags Controller
 *
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
	
	//set the pagination ordering to be by time modified
	public $paginate = [
		'contain' => ['Uploadedfiles'],
		'limit' => 30,
		'order' => ['Uploadedfiles.modified' => 'desc']
	];
	
	public function initialize(){
		parent::initialize();
		$this->loadModel('Uploadedfiles');
	}

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
		if (isset($_GET['query'])){
			$str = '%'.$_GET['query'].'%';
			//get the meeting date maybe
			$query = $this->Tags->find()
				->where(['label like' => $str]);//get the relevant minutes
			$tags = $this->paginate($query);
		} else {
			$tags = $this->paginate($this->Tags);
		}
        $this->set('tags', $tags);
        $this->set('_serialize', ['tags']);
    }
    
    //Used for tag autocomplete
    public function list_tags(){
		if (isset($_GET['query'])){
			$str = '%'.$_GET['query'].'%';
			//get the meeting date maybe
			$tags = $this->Tags->find('all')
				->select('label')
				->where(['label like' => $str])//get the relevant minutes
				->limit(30)
				->hydrate(false)
				->toArray();
		} else {
			$tags = $this->Tags->find('all')
				->select('label')
				->limit(30)
				->hydrate(false)
				->toArray();
		}
		$tag_names = array();
		foreach ($tags as $tag){
			$tag_names[] = $tag['label'];
		}
		echo json_encode($tag_names);
		exit;
	}

    /**
     * View method
     *
     * @param string|null $id Tag id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $tag = $this->Tags->get($id, [
            'contain' => ['Uploadedfiles']
        ]);
        $this->set('tag', $tag);
        $this->set('_serialize', ['tag']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $tag = $this->Tags->newEntity();
        if ($this->request->is('post')) {
            $tag = $this->Tags->patchEntity($tag, $this->request->data);
            if ($this->Tags->save($tag)) {
                $this->Flash->success(__('The tag has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The tag could not be saved. Please, try again.'));
            }
        }
        $uploadedfiles = $this->Tags->Uploadedfiles->find('all', ['limit' => 200]);
        $this->set(compact('tag', 'uploadedfiles'));
        $this->set('_serialize', ['tag']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Tag id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $tag = $this->Tags->get($id, [
            'contain' => ['Uploadedfiles']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $tag = $this->Tags->patchEntity($tag, $this->request->data);
            if ($this->Tags->save($tag)) {
                $this->Flash->success(__('The tag has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The tag could not be saved. Please, try again.'));
            }
        }
        $uploadedfiles = $this->Uploadedfiles->find('all', ['limit' => 200]);
        $this->set(compact('tag', 'uploadedfiles'));
        $this->set('_serialize', ['tag']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Tag id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $tag = $this->Tags->get($id);
        if ($this->Tags->delete($tag)) {
            $this->Flash->success(__('The tag has been deleted.'));
        } else {
            $this->Flash->error(__('The tag could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
