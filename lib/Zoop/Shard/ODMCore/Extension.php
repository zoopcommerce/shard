<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Zoop\Shard\AbstractExtension;

/**
 * Defines the resouces this extension requires
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Extension extends AbstractExtension
{

    protected $subscribers = [
        'subscriber.odmcore.boostrappedsubscriber',
        'subscriber.odmcore.exceptioneventsaggregator',
        'subscriber.odmcore.flushsubscriber',
        'subscriber.odmcore.preloadsubscriber',
        'subscriber.odmcore.loadmetadatasubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.odmcore.boostrappedsubscriber' =>
                'Zoop\Shard\ODMCore\BootstrappedSubscriber',
            'subscriber.odmcore.flushsubscriber' =>
                'Zoop\Shard\ODMCore\FlushSubscriber',
            'subscriber.odmcore.preloadsubscriber' =>
                'Zoop\Shard\ODMCore\PreLoadSubscriber',
            'subscriber.odmcore.loadmetadatasubscriber' =>
                'Zoop\Shard\ODMCore\LoadMetadataSubscriber',
            'doctrine.cache.array' => 'Doctrine\Common\Cache\ArrayCache'
        ],
        'factories' => [
            'doctrine.cache.juggernaut'                    => 'Zoop\Shard\ODMCore\JuggernautFileSystemCacheFactory',
            'modelmanager'                                 => 'Zoop\Shard\ODMCore\DevDocumentManagerFactory',
            'subscriber.odmcore.exceptioneventsaggregator' => 'Zoop\Shard\ODMCore\ExceptionEventsAggregatorFactory'
        ]
    ];

    protected $defaultDb = 'zoop-shard';

    protected $proxyDir;

    protected $hydratorDir;

    protected $metadataCache = 'doctrine.cache.array';

    protected $metadataCacheDir;

    protected $classMetadataFactory = 'Zoop\Shard\ODMCore\ClassMetadataFactory';

    protected $filters = [
        'odmfilter' => 'Zoop\Shard\ODMCore\Filter'
    ];

    public function getDefaultDb()
    {
        return $this->defaultDb;
    }

    public function setDefaultDb($defaultDb)
    {
        $this->defaultDb = $defaultDb;
    }

    public function getProxyDir()
    {
        return $this->proxyDir;
    }

    public function setProxyDir($proxyDir)
    {
        $this->proxyDir = $proxyDir;
    }

    public function getHydratorDir()
    {
        return $this->hydratorDir;
    }

    public function setHydratorDir($hydratorDir)
    {
        $this->hydratorDir = $hydratorDir;
    }

    public function getMetadataCache()
    {
        return $this->metadataCache;
    }

    public function setMetadataCache($metadataCache)
    {
        $this->metadataCache = $metadataCache;
    }

    public function getMetadataCacheDir()
    {
        return $this->metadataCacheDir;
    }

    public function setMetadataCacheDir($metadataCacheDir)
    {
        $this->metadataCacheDir = $metadataCacheDir;
    }

    public function getClassMetadataFactory()
    {
        return $this->classMetadataFactory;
    }

    public function setClassMetadataFactory($classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    public function __construct(array $config)
    {
        $this->proxyDir =  __DIR__ . '/../../../../../../data/proxies';
        $this->hydratorDir = __DIR__ . '/../../../../../../data/hydrators';
        $this->metadataCacheDir = __DIR__ . '/../../../../../../data/metadata';

        parent::__construct($config);
    }
}
