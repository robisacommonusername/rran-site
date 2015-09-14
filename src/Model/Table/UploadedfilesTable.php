<?php
namespace App\Model\Table;

use App\Model\Entity\Uploadedfile;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Uploadedfiles Model
 *
 * @property \Cake\ORM\Association\BelongsToMany $Tags
 */
class UploadedfilesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('uploadedfiles');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Tags', [
            'foreignKey' => 'uploadedfile_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'uploadedfiles_tags'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('file_name', 'create')
            ->notEmpty('file_name');

        $validator
            ->allowEmpty('mime_type');

        $validator
            ->add('file_size', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('file_size');

        $validator
            ->add('private', 'valid', ['rule' => 'boolean'])
            ->allowEmpty('private');

        return $validator;
    }
    
    	public function beforeSave($event, $entity, $options){
		    if ($entity->tag_string) {
		        $entity->tags = $this->_buildTags($entity->tag_string);
		    }
		}
		
		protected function _buildTags($tagString){
		    $new = array_unique(array_map('trim', explode(',', $tagString)));
		    $out = [];
		    $query = $this->Tags->find()
		        ->where(['Tags.label IN' => $new]);
		
		    // Remove existing tags from the list of new tags.
		    foreach ($query->extract('label') as $existing) {
		        $index = array_search($existing, $new);
		        if ($index !== false) {
		            unset($new[$index]);
		        }
		    }
		    // Add existing tags.
		    foreach ($query as $tag) {
		        $out[] = $tag;
		    }
		    // Add new tags.
		    foreach ($new as $tag) {
				//Create the new tag in the database
				$new_tag = $this->Tags->newEntity(['label' => $tag]);
				$this->Tags->save($new_tag);
		        $out[] = $new_tag;
		    }
		    return $out;
		}
}
