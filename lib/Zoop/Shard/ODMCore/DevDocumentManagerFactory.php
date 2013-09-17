<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DevDocumentManagerFactory implements FactoryInterface
{
    /**
     *
     * @param  \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $manifest = $serviceLocator->get('manifest');
        $extension = $serviceLocator->get('extension.odmcore');

        $config = new Configuration();

        $config->setProxyDir($extension->getProxyDir());
        $config->setProxyNamespace('Proxies');

        $config->setHydratorDir($extension->getHydratorDir());
        $config->setHydratorNamespace('Hydrators');

        $config->setDefaultDB($extension->getDefaultDb());

        $config->setClassMetadataFactoryName($extension->getClassMetadataFactory());

        $config->setMetadataCacheImpl(new ArrayCache);

        //create driver chain
        $chain  = new MappingDriverChain;

        foreach ($manifest->getModelMap() as $namespace => $path) {
            $driver = new AnnotationDriver(new AnnotationReader, $path);
            $chain->addDriver($driver, $namespace);
        }
        $config->setMetadataDriverImpl($chain);

        //register filters
        foreach ($extension->getFilters() as $name => $class) {
            $config->addFilter($name, $class);
        }

        //create event manager
        $eventManager = $serviceLocator->get('eventmanager');
        foreach ($manifest->getSubscribers() as $subscriber) {
            $eventManager->addEventSubscriber($serviceLocator->get($subscriber));
        }

        //register annotations
        AnnotationRegistry::registerLoader(
            function ($className) {
                return class_exists($className);
            }
        );

        $conn = new Connection(null, array(), $config);

        return ModelManager::create($conn, $config, $eventManager);
    }
}
