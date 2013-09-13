<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Reference;

use Zoop\Shard\Core\ObjectManagerAwareInterface;
use Zoop\Shard\Core\ObjectManagerAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ReferenceMap implements ObjectManagerAwareInterface
{

    use ObjectManagerAwareTrait;

    protected $cacheId = 'Zoop\Shard\Reference\ReferenceMap';

    protected $map = null;

    public function getCacheId()
    {
        return $this->cacheId;
    }

    public function setCacheId($cacheId)
    {
        $this->cacheId = $cacheId;
    }

    public function has($endpoint)
    {
        return array_key_exists($endpoint, $this->getMap());
    }

    public function get($endpoint)
    {
        return $this->getMap()[$endpoint];
    }

    public function getMap()
    {
        if (isset($this->map)) {
            return $this->map;
        }

        $cacheDriver = $this->objectManager->getConfiguration()->getMetadataCacheImpl();

        if ($cacheDriver->contains($this->cacheId)) {
            $this->map = $cacheDriver->fetch($this->cacheId);
        } else {
            $this->map = [];
            foreach ($this->objectManager->getMetadataFactory()->getAllMetadata() as $metadata) {
                foreach ($metadata->associationMappings as $mapping) {
                    if (isset($mapping['reference']) && $mapping['reference'] && $mapping['isOwningSide']) {
                        $this->map[$mapping['targetDocument']][] = [
                            'class' => $metadata->name,
                            'field'    => $mapping['name'],
                            'type'     => $mapping['type']
                        ];
                    }
                }
            }
            $cacheDriver->save($this->cacheId, $this->map);
        }

        return $this->map;
    }
}
