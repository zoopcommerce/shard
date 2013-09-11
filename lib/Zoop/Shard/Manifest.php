<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

/**
 * Pass this class a configuration array with extension namespaces, and then retrieve the
 * required filters, subscribers, and document locations
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Manifest extends AbstractExtension
{
    protected $defaultServiceManagerConfig = [
        'invokables' => [
            'documentManagerDelegatorFactory' => 'Zoop\Shard\DocumentManagerDelegatorFactory'
        ],
        'factories' => [
            'extension.accessControl'   => 'Zoop\Shard\AccessControl\ExtensionFactory',
            'extension.annotation'      => 'Zoop\Shard\Annotation\ExtensionFactory',
            'extension.core'            => 'Zoop\Shard\Core\ExtensionFactory',
            'extension.crypt'           => 'Zoop\Shard\Crypt\ExtensionFactory',
            'extension.freeze'          => 'Zoop\Shard\Freeze\ExtensionFactory',
            'extension.generator'       => 'Zoop\Shard\Generator\ExtensionFactory',
            'extension.odmcore'         => 'Zoop\Shard\ODMCore\ExtensionFactory',
            'extension.owner'           => 'Zoop\Shard\Owner\ExtensionFactory',
            'extension.reference'       => 'Zoop\Shard\Reference\ExtensionFactory',
            'extension.rest'            => 'Zoop\Shard\Rest\ExtensionFactory',
            'extension.serializer'      => 'Zoop\Shard\Serializer\ExtensionFactory',
            'extension.softdelete'      => 'Zoop\Shard\SoftDelete\ExtensionFactory',
            'extension.stamp'           => 'Zoop\Shard\Stamp\ExtensionFactory',
            'extension.state'           => 'Zoop\Shard\State\ExtensionFactory',
            'extension.validator'       => 'Zoop\Shard\Validator\ExtensionFactory',
            'extension.zone'            => 'Zoop\Shard\Zone\ExtensionFactory',
            'subscriber.lazySubscriber' => 'Zoop\Shard\LazySubscriberFactory',
        ],
    ];

    protected $dependencies = [
        'extension.odmcore' => true
    ];

    /**
     * Keys are extension namespaces
     * Values are extensionConfig objects
     *
     * @var array
     */
    protected $extensionConfigs = [];

    protected $lazySubscriberConfig;

    protected $documentManager;

    protected $serviceManager;

    protected $initalized = false;

    public function getExtensionConfigs()
    {
        return $this->extensionConfigs;
    }

    public function setExtensionConfigs(array $extensionConfigs)
    {
        $this->extensionConfigs = $extensionConfigs;
    }

    public function getLazySubscriberConfig()
    {
        return $this->lazySubscriberConfig;
    }

    public function setLazySubscriberConfig($lazySubscriberConfig)
    {
        $this->lazySubscriberConfig = $lazySubscriberConfig;
    }

    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    public function setDocumentManager($documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        $this->initalize();
        return $this->serviceManager;
    }

    public function getInitalized()
    {
        return $this->initalized;
    }

    public function setInitalized($initalized)
    {
        $this->initalized = $initalized;
    }

    protected function initalize()
    {
        if ($this->initalized) {
            return;
        }
        $this->initalized = true;

        if (isset($this->serviceManager)) {
            $serviceManager = $this->serviceManager;
        } else {
            $this->defaultServiceManagerConfig['delegators'][$this->documentManager] =
                ['documentManagerDelegatorFactory'];
            $serviceManager = self::createServiceManager($this->defaultServiceManagerConfig);
            $this->serviceManager = $serviceManager;
        }
        $serviceManager->setService('manifest', $this);

        //initalize extensions
        foreach ($this->extensionConfigs as $name => $extensionConfig) {
            if (!$extensionConfig) {
                unset($this->extensionConfigs[$name]);
                continue;
            }
            $this->expandExtensionConfig($name);
        }
        foreach ($this->dependencies as $name => $extensionConfig) {
            if (!$extensionConfig) {
                continue;
            }
            $this->expandExtensionConfig($name);
        }

        //merge all the configs
        $config = [
            'service_manager_config' => [],
            'filters' => [],
            'documents' => [],
            'cli_commands' => [],
            'cli_helpers' => []
        ];
        foreach ($this->extensionConfigs as $extensionConfig) {
            $config = ArrayUtils::merge(
                $config,
                array_intersect_key(
                    $extensionConfig,
                    $config
                )
            );
        }
        $this->serviceManagerConfig = ArrayUtils::merge($config['service_manager_config'], $this->serviceManagerConfig);
        $this->filters = ArrayUtils::merge($config['filters'], $this->filters);
        $this->documents = ArrayUtils::merge($config['documents'], $this->documents);
        $this->cliCommands = ArrayUtils::merge($config['cli_commands'], $this->cliCommands);
        $this->cliHelpers = ArrayUtils::merge($config['cli_helpers'], $this->cliHelpers);

        //Apply service manager config
        $serviceManagerConfig = new Config($this->serviceManagerConfig);
        $serviceManagerConfig->configureServiceManager($serviceManager);

        //Make sure default service manager config is included in the main service manager config variable
        $this->serviceManagerConfig = ArrayUtils::merge(
            $this->defaultServiceManagerConfig,
            $this->serviceManagerConfig
        );

        //create lazySubscriber configuration
        $lazySubscriberConfig = [];
        foreach ($this->extensionConfigs as $extensionConfig) {
            foreach ($extensionConfig['subscribers'] as $subscriber) {
                foreach ($serviceManager->get($subscriber)->getSubscribedEvents() as $event) {
                    if (! isset($lazySubscriberConfig[$event])) {
                        $lazySubscriberConfig[$event] = [];
                    }
                    $lazySubscriberConfig[$event][] = $subscriber;
                }
            }
            foreach ($this->subscribers as $subscriber) {
                foreach ($serviceManager->get($subscriber)->getSubscribedEvents() as $event) {
                    if (! isset($lazySubscriberConfig[$event])) {
                        $lazySubscriberConfig[$event] = [];
                    }
                    $lazySubscriberConfig[$event][] = $subscriber;
                }
            }
        }
        $this->lazySubscriberConfig = $lazySubscriberConfig;
        $this->subscribers = ['subscriber.lazySubscriber'];
    }

    protected function expandExtensionConfig($name)
    {
        //Get extension
        $extension = $this->serviceManager->get($name);

        //ensure dependencies get expaned also
        foreach ($extension->getDependencies() as $dependencyName => $dependencyConfig) {
            if (! isset($this->extensionConfigs[$dependencyName]) ||
                is_bool($this->extensionConfigs[$dependencyName])
            ) {
                $this->expandExtensionConfig($dependencyName);
            }
        }

        $this->extensionConfigs[$name] = $extension->toArray();
    }

    public static function createServiceManager($config = [])
    {
        $serviceManager = new ServiceManager(new Config($config));

        $serviceManager->addInitializer(
            function ($instance, ServiceLocatorInterface $serviceLocator) {
                if ($instance instanceof DocumentManagerAwareInterface) {
                    $instance->setDocumentManager(
                        $serviceLocator->get(
                            $serviceLocator->get('manifest')->getDocumentManager()
                        )
                    );
                }
            }
        );

        $serviceManager->addInitializer(
            function ($instance, ServiceLocatorInterface $serviceLocator) {
                if ($instance instanceof ServiceLocatorAwareInterface) {
                    $instance->setServiceLocator($serviceLocator);
                }
            }
        );

        return $serviceManager;
    }

    /**
     * Cast to array
     *
     * @return array
     */
    public function toArray()
    {
        $this->initalize();
        $array = parent::toArray();
        unset($array['default_service_manager_config']);
        unset($array['service_manager']);
        return $array;
    }
}
