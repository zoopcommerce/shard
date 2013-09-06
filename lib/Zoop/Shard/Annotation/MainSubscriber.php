<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation;

use Doctrine\Common\Annotations;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber
{

    protected $annotationReader;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            ODMEvents::loadClassMetadata
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        $documentManager = $eventArgs->getDocumentManager();
        $eventManager = $documentManager->getEventManager();

        if (! isset($this->annotationReader)) {
            $this->annotationReader = new Annotations\AnnotationReader;
            $this->annotationReader = new Annotations\CachedReader(
                $this->annotationReader,
                $documentManager->getConfiguration()->getMetadataCacheImpl()
            );
        }

        //Inherit document annotations from parent classes
        if (count($metadata->parentClasses) > 0) {
            foreach ($metadata->parentClasses as $parentClass) {
                $this->buildMetadata($documentManager->getClassMetadata($parentClass), $metadata, $eventManager);
            }
        }

        $this->buildMetadata($metadata, $metadata, $eventManager);

        // Raise post build metadata event
        if ($eventManager->hasListeners(Events::POST_BUILD_METADATA)) {
            $eventManager->dispatchEvent(
                Events::POST_BUILD_METADATA,
                $eventArgs
            );
        }
    }

    protected function buildMetadata(ClassMetadata $source, ClassMetadata $target, $eventManager)
    {
        $this->processDocumentAnnotations($source, $target, $eventManager);
        $this->processFieldAnnotations($source, $target, $eventManager);
        $this->processMethodAnnotations($source, $target, $eventManager);
    }

    protected function processDocumentAnnotations(ClassMetadata $source, ClassMetadata $target, $eventManager)
    {
        $sourceReflClass = $source->getReflectionClass();
        $targetReflClass = $target->getReflectionClass();

        //Document annotations
        foreach ($this->annotationReader->getClassAnnotations($sourceReflClass) as $annotation) {
            if (defined(get_class($annotation) . '::EVENT')) {

                // Raise annotation event
                if ($eventManager->hasListeners($annotation::EVENT)) {
                    $eventManager->dispatchEvent(
                        $annotation::EVENT,
                        new AnnotationEventArgs(
                            $target,
                            EventType::DOCUMENT,
                            $annotation,
                            $targetReflClass,
                            $eventManager
                        )
                    );
                }
            }
        }
    }

    protected function processFieldAnnotations(ClassMetadata $source, ClassMetadata $target, $eventManager)
    {
        $sourceReflClass = $source->getReflectionClass();

        //Field annotations
        foreach ($sourceReflClass->getProperties() as $reflField) {
            foreach ($this->annotationReader->getPropertyAnnotations($reflField) as $annotation) {
                if (defined(get_class($annotation) . '::EVENT')) {

                    // Raise annotation event
                    if ($eventManager->hasListeners($annotation::EVENT)) {
                        $eventManager->dispatchEvent(
                            $annotation::EVENT,
                            new AnnotationEventArgs($target, EventType::FIELD, $annotation, $reflField, $eventManager)
                        );
                    }
                }
            }
        }
    }

    protected function processMethodAnnotations(ClassMetadata $source, ClassMetadata $target, $eventManager)
    {
        $sourceReflClass = $source->getReflectionClass();

        //Method annotations
        foreach ($sourceReflClass->getMethods() as $reflMethod) {

            foreach ($this->annotationReader->getMethodAnnotations($reflMethod) as $annotation) {
                if (defined(get_class($annotation) . '::EVENT')) {

                    // Raise annotation event
                    if ($eventManager->hasListeners($annotation::EVENT)) {
                        $eventManager->dispatchEvent(
                            $annotation::EVENT,
                            new AnnotationEventArgs($target, EventType::METHOD, $annotation, $reflMethod, $eventManager)
                        );
                    }
                }
            }
        }
    }
}
