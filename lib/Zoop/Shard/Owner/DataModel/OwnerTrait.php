<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Owner\DataModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * Implementation of Zoop\Common\Owner\OwnerInterface
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait OwnerTrait
{
    /**
     * @ODM\String
     * @Shard\Owner
     * @Shard\Validator\Chain({
     *     @Shard\Validator\Required,
     *     @Shard\Validator\Slug
     * })
     */
    protected $owner;

    /**
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     *
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->owner = (string) $owner;
    }
}
