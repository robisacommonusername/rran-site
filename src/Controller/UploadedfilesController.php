<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Uploadedfiles Controller
 *
 * @property \App\Model\Table\UploadedfilesTable $Uploadedfiles
 */
class UploadedfilesController extends AppController
{
	
	//set the pagination ordering to be by time modified
	public $paginate = [
		'contain' => ['Tags'],
		'limit' => 30,
		'order' => ['Uploadedfiles.modified' => 'desc']
	];
	
	public function initialize(){
		parent::initialize();
		$this->loadModel('Tags');
		$this->loadComponent('Upload', ['private' => true,
			'encrypt' => false,
			'fields' => [
				'key' => 'content_key',
				'content_type' => 'mime_type',
				'file_name', 'file_size', 'private'
			]]
		);
				
	}

    /**
     * Index method
     *
     * @return void
     */
     
     //Set up authorisations
     public function isAuthorized($user){
		 //Admin can do everything
		 if ($user['is_admin']){
			 return true;
		 }
		 
		 //Anyone can use the public view method
		 if ($this->request->action === 'public_view'){
			 return true;
		 }
		 
		 //Non-admin can perform all actions on a private (but pre-existing) file
		 if (!$user['is_admin'] && in_array($this->request->action, ['edit','view','delete'])) {
			 $file_id = (int) $this->request->params['pass'][0];
			 $uploadedfile = $this->Uploadedfiles->get($file_id);
			 if ($uploadedfile['private']) {
				 return true;
			 }
		 }
		 
		 //Non-admin can also add a file, provided the data passed in the
		 //request specifies that the file is to be private
		 if (!$user['is_admin'] && $this->request->action === 'add' && $this->request->data['private']){
			 return true;
		 }
		 
		 //Public file by non-admin user can only be viewed
		 if (!$user['is_admin'] && $this->request->action === 'view' && !$uploadedfile['private']){
			 return true;
		 }
		 
		 //Everything else is forbidden
		 return false;
	 }
	 
    public function index()
    {
		if (isset($_GET['query'])){
			//Search by filename and by tag
			$str = $_GET['query'];
			$files_by_name = $this->Uploadedfiles->find('all')
				->where(['file_name like' => "%$str%"]);
			
			//$files_by_tag = $this->Uploadedfiles->find('all')
			//	->matching('Tags', function($q) use($str){
			//		return $q->where(['Tags.label like' => "%$str%"]);
			//	});
			$files = $this->paginate($files_by_name);
			
		} else {
			
			$files = $this->paginate($this->Uploadedfiles);
		}
        $this->set('uploadedfiles', $files);
        $this->set('_serialize', ['uploadedfiles']);
    }

    /**
     * View method
     *
     * @param string|null $id Uploadedfile id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $uploadedfile = $this->Uploadedfiles->get($id, [
            'contain' => ['Tags']
        ]);
        //$this->set('uploadedfile', $uploadedfile);
        //$this->set('_serialize', ['uploadedfile']);
        $this->Upload->downloadFromEntity($uploadedfile);
        
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $uploadedfile = $this->Uploadedfiles->newEntity();
        if ($this->request->is('post')) {
			
			//only admin can upload public file
			$private = true;
			if ($this->Auth->user('is_admin')) {
				$private = (bool) $this->request->data['private'];
			}
			
			//set data on entity
			$uploadedfile = $this->Uploadedfiles->patchEntity($uploadedfile, $this->request->data);
			$uploadedfile['private'] = $private;
			
			//Attach file to the $uploadedfile entity
			$ret = $this->Upload->attachToEntity($uploadedfile,
				$this->request->data['uploaded_file'],
				['private' => $private]
			);
			
			
			if ($ret['success'] && $this->Uploadedfiles->save($uploadedfile)){
				$this->Flash->success(__('The uploaded file has been saved.'));
				return $this->redirect(['action' => 'index']);
			}
			
			//if we haven't been re-directed yet, we've failed
			$msg = $ret['message'];
            $this->Flash->error(__("The uploaded file could not be saved. Error message was: '$msg'"));
            
        }
        $tags = $this->Uploadedfiles->Tags->find('list', ['limit' => 200]);
        $this->set(compact('uploadedfile', 'tags'));
        $this->set('_serialize', ['uploadedfile']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Uploadedfile id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $uploadedfile = $this->Uploadedfiles->get($id, [
            'contain' => ['Tags']
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
			
			//Need to check for the case of changing privacy. In that
			//case we will need to move the file
			if (isset($this->request->data['private'])){
				$new_private = (bool) $this->request->data['private'];
				$uploadedfile = $this->Upload->setEntityAttachmentPrivacy($uploadedfile, $new_private);
			}
			
            $uploadedfile = $this->Uploadedfiles->patchEntity($uploadedfile, $this->request->data);
            
            if ($this->Uploadedfiles->save($uploadedfile)) {
                $this->Flash->success(__('The uploadedfile has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The uploadedfile could not be saved. Please, try again.'));
            }
        }
        $tags = $this->Uploadedfiles->Tags->find('list', ['limit' => 200]);
        $this->set(compact('uploadedfile', 'tags'));
        $this->set('_serialize', ['uploadedfile']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Uploadedfile id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $uploadedfile = $this->Uploadedfiles->get($id);
        if ($this->Uploadedfiles->delete($uploadedfile) &&
			$this->Upload->detachFromEntity($uploadedfile)){
				
            $this->Flash->success(__('The uploaded file has been deleted.'));
        } else {
            $this->Flash->error(__('The uploaded file could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
    
    public function public_view($id = null){
		//essentially the same as view, but can be done without authentication
		//used for public viewing of public files
		$uploadedfile = $this->Uploadedfiles->get($id);
		if (!$uploadedfile['private']) {
			$this->Upload->downloadFromEntity($uploadedfile);
		} else {
			die('You do not have permission to access this file');
		}
	}
    
}
