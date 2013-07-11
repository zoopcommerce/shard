<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Generator;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ResourceMapFactory implements FactoryInterface
{

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $extension = $serviceLocator->get('extension.generator');
        $instance = new ResourceMap;

        $instance->setMap($extension->getResourceMap());

        return $instance;
    }
}
