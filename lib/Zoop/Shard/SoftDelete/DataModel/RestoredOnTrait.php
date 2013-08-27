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
trait RestoredOnTrait
{
    /**
     * @ODM\Timestamp
     * @Shard\SoftDelete\RestoredOn
     */
    protected $restoredOn;

    /**
     *
     * @return timestamp
     */
    public function getRestoredOn()
    {
        return $this->restoredOn;
    }
}
