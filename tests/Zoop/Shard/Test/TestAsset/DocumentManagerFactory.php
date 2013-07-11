<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Test\TestAsset;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DocumentManagerFactory implements FactoryInterface
{

    const DEFAULT_DB = 'zoop_shard_tests';

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $manifest = $serviceLocator->get('manifest');

        $config = new Configuration();

        $config->setProxyDir(__DIR__ . '/../../../../Proxies');
        $config->setProxyNamespace('Proxies');

        $config->setHydratorDir(__DIR__ . '/../../../../Hydrators');
        $config->setHydratorNamespace('Hydrators');

        $config->setDefaultDB(self::DEFAULT_DB);

        $config->setMetadataCacheImpl(new ArrayCache);

        //create driver chain
        $chain  = new MappingDriverChain;

        foreach ($manifest->getDocuments() as $namespace => $path){
            $driver = new AnnotationDriver(new AnnotationReader, $path);
            $chain->addDriver($driver, $namespace);
        }
        $config->setMetadataDriverImpl($chain);

        //register filters
        foreach ($manifest->getFilters() as $name => $class){
            $config->addFilter($name, $class);
        }

        //create event manager
        $eventManager = new EventManager();
        foreach($manifest->getSubscribers() as $subscriber){
            $eventManager->addEventSubscriber($serviceLocator->get($subscriber));
        }

        //register annotations
        AnnotationRegistry::registerLoader(function($className) {
            return class_exists($className);
        });

        $conn = new Connection(null, array(), $config);

        return DocumentManager::create($conn, $config, $eventManager);
    }
}