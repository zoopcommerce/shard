<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete\DataModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait RestoredByTrait {

    /**
     * @ODM\String
     * @ODM\Index
     * @Shard\SoftDelete\RestoredBy
     * @Shard\Validator\Slug
     */
    protected $restoredBy;

    /**
     *
     * @return string
     */
    public function getRestoredBy(){
        return $this->restoredBy;
    }
}
