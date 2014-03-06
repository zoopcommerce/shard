<?php

namespace Zoop\Shard\ODMCore;

use Zoop\Juggernaut\Adapter\FileSystem;
use Zoop\Shard\ODMCore\JuggernautCache;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class JuggernautFileSystemCacheFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $juggernautInstance = new FileSystem($serviceLocator->get('extension.odmcore')->getMetadataCacheDir());
        $juggernautInstance->setTtl(2419200); //one month

        return new JuggernautCache($juggernautInstance);
    }
}
