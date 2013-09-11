<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zoop\Shard\Core\Events;
use Zoop\Shard\Core\LoadMetadataEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::LOAD_METADATA];
    }


    public function loadMetadata(LoadMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $eventManager = $eventArgs->getEventManager();
        $annotationReader = $eventArgs->getAnnotationReader();

        //Inherit document annotations from parent classes
        foreach ($eventArgs->getParentMetadata() as $parentMetadata) {
            $this->buildMetadata($parentMetadata, $metadata, $annotationReader, $eventManager);
        }

        $this->buildMetadata($metadata, $metadata, $annotationReader, $eventManager);
    }

    protected function buildMetadata(ClassMetadata $source, ClassMetadata $target, $annotationReader, $eventManager)
    {
        $this->processDocumentAnnotations($source, $target, $annotationReader, $eventManager);
        $this->processFieldAnnotations($source, $target, $annotationReader, $eventManager);
        $this->processMethodAnnotations($source, $target, $annotationReader, $eventManager);
    }

    protected function processDocumentAnnotations(ClassMetadata $source, ClassMetadata $target, $annotationReader, $eventManager)
    {
        $sourceReflClass = $source->getReflectionClass();
        $targetReflClass = $target->getReflectionClass();

        //Document annotations
        foreach ($annotationReader->getClassAnnotations($sourceReflClass) as $annotation) {
            if (defined(get_class($annotation) . '::EVENT')) {

                // Raise annotation event
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

    protected function processFieldAnnotations(ClassMetadata $source, ClassMetadata $target, $annotationReader, $eventManager)
    {
        $sourceReflClass = $source->getReflectionClass();

        //Field annotations
        foreach ($sourceReflClass->getProperties() as $reflField) {
            foreach ($annotationReader->getPropertyAnnotations($reflField) as $annotation) {
                if (defined(get_class($annotation) . '::EVENT')) {

                    // Raise annotation event
                    $eventManager->dispatchEvent(
                        $annotation::EVENT,
                        new AnnotationEventArgs($target, EventType::FIELD, $annotation, $reflField, $eventManager)
                    );
                }
            }
        }
    }

    protected function processMethodAnnotations(ClassMetadata $source, ClassMetadata $target, $annotationReader, $eventManager)
    {
        $sourceReflClass = $source->getReflectionClass();

        //Method annotations
        foreach ($sourceReflClass->getMethods() as $reflMethod) {

            foreach ($annotationReader->getMethodAnnotations($reflMethod) as $annotation) {
                if (defined(get_class($annotation) . '::EVENT')) {

                    // Raise annotation event
                    $eventManager->dispatchEvent(
                        $annotation::EVENT,
                        new AnnotationEventArgs($target, EventType::METHOD, $annotation, $reflMethod, $eventManager)
                    );
                }
            }
        }
    }
}
