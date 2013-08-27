<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze\DataModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Implements \Zoop\Common\Freeze\FrozenOnInterface
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait FrozenOnTrait
{
    /**
     * @ODM\Timestamp
     * @Shard\Freeze\FrozenOn
     */
    protected $frozenOn;

    /**
     *
     * @return timestamp
     */
    public function getFrozenOn()
    {
        return $this->frozenOn;
    }
}
