<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class LazySubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $config = [];

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getSubscribedEvents()
    {
        return array_keys($this->config);
    }

    public function __call($event, $arguments)
    {
        foreach ($this->config[$event] as $subscriber) {
            call_user_func_array([$this->serviceLocator->get($subscriber), $event], $arguments);
        }
    }
}
