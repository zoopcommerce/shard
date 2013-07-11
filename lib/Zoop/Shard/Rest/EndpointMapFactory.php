<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Rest;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class EndpointMapFactory implements FactoryInterface
{

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $extension = $serviceLocator->get('extension.rest');
        $instance = new EndpointMap;

        $instance->setMap($extension->getEndpointMap());

        return $instance;
    }
}
