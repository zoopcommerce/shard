<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze\DataModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Implements \Zoop\Common\Freeze\FrozenByInterface
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait FrozenByTrait {

    /**
     * @ODM\String
     * @ODM\Index
     * @Shard\Freeze\FrozenBy
     * @Shard\Validator\Slug
     */
    protected $frozenBy;

    /**
     *
     * @return string
     */
    public function getFrozenBy(){
        return $this->frozenBy;
    }
}
