<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard;

use Zend\Stdlib\AbstractOptions;

/**
 * A base class which extensions configs must extend
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
abstract class AbstractExtension extends AbstractOptions
{
    /**
     * List of other extensions which must be loaded
     * for this extension to work
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * List of events that are triggered instead of raising an exception
     *
     * @var array
     */
    protected $exceptionEvents = [];

    protected $models = [];

    protected $subscribers = [];

    protected $serviceManagerConfig = [];

    /**
     *
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     *
     * @param array $dependencies
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function getExceptionEvents()
    {
        return $this->exceptionEvents;
    }

    public function setExceptionEvents($exceptionEvents)
    {
        $this->exceptionEvents = $exceptionEvents;
    }

    public function getModels()
    {
        return $this->models;
    }

    public function setModels($models)
    {
        $this->models = $models;
    }

    public function getServiceManagerConfig()
    {
        return $this->serviceManagerConfig;
    }

    public function setServiceManagerConfig($serviceManagerConfig)
    {
        $this->serviceManagerConfig = $serviceManagerConfig;
    }

    public function getSubscribers()
    {
        return $this->subscribers;
    }

    public function setSubscribers($subscribers)
    {
        $this->subscribers = $subscribers;
    }
}
