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
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Arguments for annotation events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AnnotationEventArgs extends BaseEventArgs
{
    protected $metadata;

    protected $eventType;

    protected $annotation;

    protected $reflection;

    protected $eventManager;

    /**
     *
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     * @param type                                               $eventType
     * @param \Doctrine\Common\Annotations\Annotation            $annotation
     * @param type                                               $reflection
     * @param \Doctrine\Common\EventManager                      $eventManager
     */
    public function __construct(
        ClassMetadata $metadata,
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

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getEventType()
    {
        return $this->eventType;
    }

    public function getAnnotation()
    {
        return $this->annotation;
    }

    public function getReflection()
    {
        return $this->reflection;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }
}
