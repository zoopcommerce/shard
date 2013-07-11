<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Stamp\DataModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotations as Shard;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait UpdatedByTrait {

    /**
     * @ODM\String
     * @Shard\Stamp\UpdatedBy
     */
    protected $updatedBy;

    /**
     *
     * @return string
     */
    public function getUpdatedBy(){
        return $this->updatedBy;
    }
}

