<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State\DataModel;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotations as Shard;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait StateTrait
{
    /**
     * @ODM\String
     * @ODM\Index
     * @Shard\State
     * @Shard\Validator\Slug
     */
    protected $state;

    /**
     * Set the current resource state
     *
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = (string) $state;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
}
