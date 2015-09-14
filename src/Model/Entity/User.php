<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * User Entity.
 */
class User extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     * Note that '*' is set to true, which allows all unspecified fields to be
     * mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
		'password' => false,
        'id' => false,
        'email' => true,
        'real_name' => true,
        'phone' => true,
        'is_admin' => false,
        'username' => true
    ];
    
    //Return the "real name" if it's set, otherwise return the username
    public function displayName(){
		if (isset($this->_properties['real_name'])){
			return $this->_properties['real_name'];
		}
		if (isset($this->_properties['username'])){
			return $this->_properties['username'];
		}
		return '';
	}
    
    //password hashing
    protected function _setPassword($value) {
        $hasher = new DefaultPasswordHasher();
        return $hasher->hash($value);
    }
}
