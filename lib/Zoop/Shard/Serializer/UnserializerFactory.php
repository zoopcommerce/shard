<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class UnserializerFactory implements FactoryInterface
{

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $extension = $serviceLocator->get('extension.serializer');
        $instance = new Unserializer;

        $instance->setTypeSerializers($extension->getTypeSerializers());

        return $instance;
    }
}
