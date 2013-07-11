<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\EventArgs as BaseEventArgs;
use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;

/**
 * Arguments for annotation events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AnnotationEventArgs extends BaseEventArgs {

    protected $metadata;

    protected $eventType;

    protected $annotation;

    protected $reflection;

    protected $eventManager;

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo $metadata
     * @param string $eventType
     * @param \Doctrine\Common\Annotations\Annotation $annotation
     * @param mixed $reflection
     */
    public function __construct(
        ClassMetadataInfo $metadata,
        $eventType,
        Annotation $annotation,
        $reflection,
        EventManager $eventManager
    ) {
        $this->metadata = $metadata;
        $this->eventType = $eventType;
        $this->annotation = $annotation;
        $this->reflection = $reflection;
        $this->eventManager = $eventManager;
    }

    public function getMetadata() {
        return $this->metadata;
    }

    public function getEventType() {
        return $this->eventType;
    }

    public function getAnnotation() {
        return $this->annotation;
    }

    public function getReflection() {
        return $this->reflection;
    }

    public function getEventManager() {
        return $this->eventManager;
    }

}