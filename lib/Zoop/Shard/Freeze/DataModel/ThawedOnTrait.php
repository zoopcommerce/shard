<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze\DataModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Implements \Zoop\Common\Freeze\ThawedOnInterface
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait ThawedOnTrait {

    /**
     * @ODM\Timestamp
     * @Shard\Freeze\ThawedOn
     */
    protected $thawedOn;

    /**
     *
     * @return timestamp
     */
    public function getThawedOn(){
        return $this->thawedOn;
    }
}
