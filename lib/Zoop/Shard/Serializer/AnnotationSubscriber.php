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
            Shard\Serializer\ClassName::EVENT,
            Shard\Serializer\Discriminator::EVENT,
            Shard\Serializer\Eager::EVENT,
            Shard\Serializer\Ignore::EVENT,
            Shard\Serializer\RefLazy::EVENT,
            Shard\Serializer\ReferenceSerializer::EVENT,
            Shard\Serializer\SimpleLazy::EVENT,
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerClassName(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $annotation = $eventArgs->getAnnotation();
        $this->createMetadata($metadata);
        $metadata->serializer['className'] = (boolean) $annotation->value;
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerDiscriminator(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $annotation = $eventArgs->getAnnotation();
        $this->createMetadata($metadata);
        $metadata->serializer['discriminator'] = (boolean) $annotation->value;
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerEager(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $this->createMetadata($metadata);
        $metadata->serializer['fields'][$eventArgs->getReflection()->getName()]['referenceSerializer'] =
            'serializer.reference.eager';
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerIgnore(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $annotation = $eventArgs->getAnnotation();
        $this->createMetadata($metadata);
        $metadata->serializer['fields'][$eventArgs->getReflection()->getName()]['ignore'] =
            $annotation->value;
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerRefLazy(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $this->createMetadata($metadata);
        $metadata->serializer['fields'][$eventArgs->getReflection()->getName()]['referenceSerializer'] =
            'serializer.reference.refLazy';
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerReferenceSerializer(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $annotation = $eventArgs->getAnnotation();
        $this->createMetadata($metadata);
        $metadata->serializer['fields'][$eventArgs->getReflection()->getName()]['referenceSerializer'] =
            $annotation->value;
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSerializerSimpleLazy(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $this->createMetadata($metadata);
        $metadata->serializer['fields'][$eventArgs->getReflection()->getName()]['referenceSerializer'] =
            'serializer.reference.simpleLazy';
    }

    protected function createMetadata($metadata)
    {
        if (! isset($metadata->serializer)) {
            $metadata->serializer = [
                'fields'   => []
            ];
        }
    }
}
