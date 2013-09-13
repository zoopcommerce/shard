<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Rest;

use Zend\Stdlib\AbstractOptions;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Endpoint extends AbstractOptions
{

    protected $name;

    protected $class;

    protected $property = 'id';

    protected $cacheControl;

    protected $embeddedLists;

    protected $parent;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function getCacheControl()
    {
        if (! $this->cacheControl instanceof CacheControl) {
            $this->cacheControl = new CacheControl($this->cacheControl);
        }

        return $this->cacheControl;
    }

    public function setCacheControl($cacheControl)
    {
        $this->cacheControl = $cacheControl;
    }

    public function getEmbeddedLists()
    {
        return $this->embeddedLists;
    }

    public function setEmbeddedLists($embeddedLists)
    {
        foreach ($embeddedLists as $property => $endpoint) {
            if (! $endpoint instanceof Endpoint) {
                $endpoint = new Endpoint($endpoint);
                $endpoint->setParent($this);
            }
            $this->embeddedLists[$property] = $endpoint;
        }
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }
}
