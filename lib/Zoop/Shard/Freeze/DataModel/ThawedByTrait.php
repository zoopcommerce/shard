<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze\DataModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait ThawedByTrait {

    /**
     * @ODM\String
     * @ODM\Index
     * @Shard\Freeze\ThawedBy
     * @Shard\Validator\Slug
     */
    protected $thawedBy;

    /**
     *
     * @return string
     */
    public function getThawedBy(){
        return $this->thawedBy;
    }
}
