<?php
namespace App\Model\Table;

use App\Model\Entity\Minute;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Minutes Model
 *
 */
class MinutesTable extends Table
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

        $this->table('minutes');
        $this->displayField('id');
        $this->primaryKey('id');
        
        $this->addBehavior('Timestamp');
        $this->addBehavior('Uploadable', [
			'private' => true,
			'encrypted' => false,
			'fields' => [
				'content_type' => 'mime_type',
				'key' => 'content_key',
				'file_name', 'file_size'
			]
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
            ->add('meeting_date', 'valid', ['rule' => 'date'])
            ->allowEmpty('meeting_date')
            ->add('meeting_date', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->requirePresence('file_name', 'create')
            ->notEmpty('file_name');

        $validator
            ->allowEmpty('mime_type');

        $validator
            ->add('file_size', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('file_size');

        $validator
            ->allowEmpty('content');

        return $validator;
    }
}
