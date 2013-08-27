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
trait SoftDeletedByTrait
{
    /**
     * @ODM\String
     * @ODM\Index
     * @Shard\SoftDelete\DeletedBy
     * @Shard\Validator\Slug
     */
    protected $softDeletedBy;

    /**
     *
     * @return string
     */
    public function getSoftDeletedBy()
    {
        return $this->softDeletedBy;
    }
}
