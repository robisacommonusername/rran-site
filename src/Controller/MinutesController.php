<?php
namespace App\Controller;
require('Component/ContentExtractor.php');
use App\Controller\AppController;

/**
 * Minutes Controller
 *
 * @property \App\Model\Table\MinutesTable $Minutes
 */
class MinutesController extends AppController
{
	public function initialize(){
		parent::initialize();
		$this->loadComponent('Upload', ['private' => true,
				'encrypt' => false,
				'fields' => [
					'key' => 'content_key',
					'content_type' => 'mime_type',
					'file_name', 'file_size'
				]]
			);
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
			$query = $this->Minutes->find()
				->where(['content like' => $str]);//get the relevant minutes
			$minutes = $this->paginate($query);
		} else {
			$minutes = $this->paginate($this->Minutes);
		}
        $this->set('minutes', $minutes);
        $this->set('_serialize', ['minutes']);
    }

    /**
     * View method
     *
     * @param string|null $id Minute id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $minute = $this->Minutes->get($id, [
            'contain' => []
        ]);
        
        $this->Upload->downloadFromEntity($minute);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $minute = $this->Minutes->newEntity();
        if ($this->request->is('post')) {
			//perform content extraction
			$f = $this->request->data['uploaded_file'];
			$extractor = new \ContentExtractor($f['type']);
			if ($extractor->supported()){
				try {
					$minute['content'] = $extractor->extract($f['tmp_name']);
				} catch (Exception $e) {
					$minute['content'] = '';
				}
			}
			
			//set meeting date
			$date = new \DateTime($this->request->data['meeting_date']);
			$minute['meeting_date'] = $date;
			
            //process upload data. Always set to private
            $ret = $this->Upload->attachToEntity($minute, $f);
			
			if ($ret['success'] && $this->Minutes->save($minute)){
				$this->Flash->success(__('The minutes have been saved.'));
				return $this->redirect(['action' => 'index']);
			}
			
			//if we haven't been re-directed yet, we've failed
			$msg = $ret['message'];
            $this->Flash->error(__("The uploaded file could not be saved. Error message was: '$msg'"));
        }
        $this->set(compact('minute'));
        $this->set('_serialize', ['minute']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Minute id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $minute = $this->Minutes->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if (isset($this->request->data['meeting_date'])){
				$minute['meeting_date'] = new \DateTime($this->request->data['meeting_date']);
			}
			
			unset($this->request->data['meeting_date']);
			$minute = $this->Minutes->patchEntity($minute, $this->request->data);
			
            if ($this->Minutes->save($minute)) {
                $this->Flash->success(__('The minute has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The minute could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('minute'));
        $this->set('_serialize', ['minute']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Minute id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $minute = $this->Minutes->get($id);
        if ($this->Minutes->delete($minute) && 
			$this->Upload->detachFromEntity($minute)){
				
            $this->Flash->success(__('The minute has been deleted.'));
        } else {
            $this->Flash->error(__('The minute could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
