<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Collection\Collection;
/**
 * Uploadedfile Entity.
 */
class Uploadedfile extends Entity
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
        'id' => false,
        'file_name' => true,
        'mime_type' => false,
        'file_size' => false,
        'private' => true,
        'tag_string' => true //computed property
    ];
    
    //Computed fields for getting and setting tags as a list of comma
    //separated values
    protected function _getTagString() {
		if (isset($this->_properties['tag_string'])) {
			return $this->_properties['tag_string'];
		}
		if (empty($this->tags)) {
			return '';
		}
		$tags = new Collection($this->tags);
		$str = $tags->reduce(function ($string, $tag) {
			return $string . $tag->label . ', ';
		}, '');
		return trim($str, ', ');
	}
}
