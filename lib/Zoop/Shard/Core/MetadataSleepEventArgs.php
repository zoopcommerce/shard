<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventArgs as BaseEventArgs;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MetadataSleepEventArgs extends BaseEventArgs
{
    protected $metadata;

    protected $eventManager;

    protected $serialized;

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function getSerialized()
    {
        return $this->serialized;
    }

    public function setSerialized($serialized)
    {
        $this->serialized = $serialized;
    }

    public function addSerialized($name)
    {
        $this->serialized[] = $name;
    }

    public function __construct(ClassMetadata $metadata, array $serialized, EventManager $eventManager)
    {
        $this->metadata = $metadata;
        $this->serialized = $serialized;
        $this->eventManager = $eventManager;
    }
}
