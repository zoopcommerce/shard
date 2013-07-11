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
 * Implements \Zoop\Common\Stamp\UpdatedOnInterface
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait UpdatedOnTrait {

    /**
     * @ODM\Timestamp
     * @Shard\Stamp\UpdatedOn
     */
    protected $updatedOn;

    /**
     *
     * @return timestamp
     */
    public function getUpdatedOn(){
        return $this->updatedOn;
    }
}
