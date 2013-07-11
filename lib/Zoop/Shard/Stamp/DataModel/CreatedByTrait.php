<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Stamp\DataModel;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotations as Shard;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait CreatedByTrait {

    /**
     * @ODM\String
     * @Shard\Stamp\CreatedBy
     */
    protected $createdBy;

    /**
     *
     * @return string
     */
    public function getCreatedBy(){
        return $this->createdBy;
    }
}
