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
        'subscriber.odmcore.mainsubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.odmcore.mainsubscriber' =>
                'Zoop\Shard\ODMCore\MainSubscriber'
        ],
        'factories' => [
            'objectmanager' => 'Zoop\Shard\ODMCore\DevDocumentManagerFactory'
        ]
    ];

    protected $defaultDb = 'zoop-shard';

    protected $proxyDir;

    protected $hydratorDir;

    protected $classMetadataFactory = 'Zoop\Shard\ODMCore\ClassMetadataFactory';

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

    public function __construct()
    {
        $this->proxyDir =  __DIR__ . '/../../../../../../data/proxies';
        $this->hydratorDir = __DIR__ . '/../../../../../../data/hydrators';
    }
}
