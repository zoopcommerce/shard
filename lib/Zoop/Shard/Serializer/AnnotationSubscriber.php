<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;

/**
 * Adds serializer values to classmetadata
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AnnotationSubscriber implements EventSubscriber
{

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Shard\Serializer\Eager::EVENT,
            Shard\Serializer\Ignore::EVENT,
            Shard\Serializer\Lazy::EVENT,
            Shard\Serializer\ReferenceSerializer::EVENT,
            Shard\Unserializer\Ignore::EVENT,
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerEager(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $serializeMetadata = $this->getSerializerMetadata($metadata);
        $serializeMetadata['fields'][$eventArgs->getReflection()->getName()]['referenceSerializer'] =
            'serializer.reference.eager';
        $metadata->setSerializer($serializeMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerIgnore(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $annotation = $eventArgs->getAnnotation();
        $serializeMetadata = $this->getSerializerMetadata($metadata);
        $serializeMetadata['fields'][$eventArgs->getReflection()->getName()]['serializeIgnore'] =
            $annotation->value;

        $metadata->setSerializer($serializeMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationUnserializerIgnore(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $annotation = $eventArgs->getAnnotation();
        $serializeMetadata = $this->getSerializerMetadata($metadata);
        $serializeMetadata['fields'][$eventArgs->getReflection()->getName()]['unserializeIgnore'] =
            $annotation->value;

        $metadata->setSerializer($serializeMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerLazy(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $serializeMetadata = $this->getSerializerMetadata($metadata);
        $serializeMetadata['fields'][$eventArgs->getReflection()->getName()]['referenceSerializer'] =
            'serializer.reference.lazy';

        $metadata->setSerializer($serializeMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerReferenceSerializer(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $annotation = $eventArgs->getAnnotation();
        $serializeMetadata = $this->getSerializerMetadata($metadata);
        $serializeMetadata['fields'][$eventArgs->getReflection()->getName()]['referenceSerializer'] =
            $annotation->value;

        $metadata->setSerializer($serializeMetadata);
    }

    protected function getSerializerMetadata($metadata)
    {
        if (!$metadata->hasProperty('serializer')) {
            $metadata->addProperty('serializer', true);
            $metadata->setSerializer(['fields' => []]);
        }

        return $metadata->getSerializer();
    }
}
