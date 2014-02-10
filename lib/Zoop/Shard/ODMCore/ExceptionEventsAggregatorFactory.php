<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ExceptionEventsAggregatorFactory implements FactoryInterface
{
    /**
     *
     * @param  \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $manifest = $serviceLocator->get('manifest');

        $subscriber = new ExceptionEventsAggregator($serviceLocator->get('eventmanager'));

        $exceptionEvents = [];
        foreach ($manifest->getExtensionConfigs() as $extensionConfig){
            $exceptionEvents = array_merge($exceptionEvents, $extensionConfig['exception_events']);
        }
        $subscriber->setExceptionEvents($exceptionEvents);

        return $subscriber;
    }
}
