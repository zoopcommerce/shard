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
trait CreatedOnTrait
{
    /**
     * @ODM\Date
     * @Shard\Stamp\CreatedOn
     */
    protected $createdOn;

    /**
     *
     * @return date
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }
}
