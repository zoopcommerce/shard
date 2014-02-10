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
                'Zoop\Shard\ODMCore\LoadMetadataSubscriber'
        ],
        'factories' => [
            'modelmanager'                                 => 'Zoop\Shard\ODMCore\DevDocumentManagerFactory',
            'subscriber.odmcore.exceptioneventsaggregator' => 'Zoop\Shard\ODMCore\ExceptionEventsAggregatorFactory'
        ]
    ];

    protected $defaultDb = 'zoop-shard';

    protected $proxyDir;

    protected $hydratorDir;

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

        parent::__construct($config);
    }
}
