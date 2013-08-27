<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Generator;

use Zoop\Shard\DocumentManagerAwareInterface;
use Zoop\Shard\DocumentManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ResourceMap implements ServiceLocatorAwareInterface, DocumentManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use DocumentManagerAwareTrait;

    protected $cacheSalt = 'Zoop\Generator_';

    protected $map = [];

    public function getMap()
    {
        return $this->map;
    }

    public function setMap(array $map)
    {
        $this->map = $map;
    }

    public function setResourceConfig($name, $config)
    {
        $this->map[$name] = $config;
    }

    public function has($name)
    {
        return isset($this->map[$name]);
    }

    public function get($name)
    {
        if (! isset($this->map[$name])) {
            throw new \Exception('Resource does not exist in resource map');
        }

        $cacheDriver = $this->documentManager->getConfiguration()->getMetadataCacheImpl();
        $id = $this->cacheSalt . $name;
        if ($resourceValue = $cacheDriver->fetch($id)) {
            return $resourceValue;
        }

        $config = $this->map[$name];
        if (isset($config['options'])) {
            $options = $config['options'];
        } else {
            $options = null;
        }

        $generator = $this->serviceLocator->get($config['generator']);
        $resource = $generator->generate($name, $config['class'], $options);

        $cacheDriver->save($id, $resource);

        return $resource;
    }
}
