<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventArgs as BaseEventArgs;
use Doctrine\Common\EventManager as BaseEventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class LoadMetadataEventArgs extends BaseEventArgs
{
    protected $metadata;

    protected $parentMetadata;

    protected $annotationReader;

    protected $eventManager;

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getParentMetadata()
    {
        return $this->parentMetadata;
    }

    public function getAnnotationReader()
    {
        return $this->annotationReader;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function __construct(
        ClassMetadata $metadata,
        array $parentMetadata,
        Reader $annotationReader,
        BaseEventManager $eventManager
    ) {
        $this->metadata = $metadata;
        $this->parentMetadata = $parentMetadata;
        $this->annotationReader = $annotationReader;
        $this->eventManager = $eventManager;
    }
}
