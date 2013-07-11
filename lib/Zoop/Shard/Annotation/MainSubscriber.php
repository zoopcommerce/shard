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
    public function getSubscribedEvents(){
        return [
            ODMEvents::loadClassMetadata
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

        if (!isset($this->annotationReader)){
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
        if ($eventManager->hasListeners(Events::postBuildMetadata)) {
            $eventManager->dispatchEvent(
                Events::postBuildMetadata,
                $eventArgs
            );
        }
    }

    protected function buildMetadata(ClassMetadata $source, ClassMetadata $target, $eventManager){

        $sourceReflClass = $source->getReflectionClass();
        $targetReflClass = $target->getReflectionClass();

        //Document annotations
        foreach ($this->annotationReader->getClassAnnotations($sourceReflClass) as $annotation) {
            if (defined(get_class($annotation) . '::event')) {

                // Raise annotation event
                if ($eventManager->hasListeners($annotation::event)) {
                    $eventManager->dispatchEvent(
                        $annotation::event,
                        new AnnotationEventArgs($target, EventType::document, $annotation, $targetReflClass, $eventManager)
                    );
                }
            }
        }

        //Field annotations
        foreach ($sourceReflClass->getProperties() as $reflField) {
            foreach ($this->annotationReader->getPropertyAnnotations($reflField) as $annotation) {
                if (defined(get_class($annotation) . '::event')) {

                    // Raise annotation event
                    if ($eventManager->hasListeners($annotation::event)) {
                        $eventManager->dispatchEvent(
                            $annotation::event,
                            new AnnotationEventArgs($target, EventType::field, $annotation, $reflField, $eventManager)
                        );
                    }
                }
            }
        }

        //Method annotations
        foreach ($sourceReflClass->getMethods() as $reflMethod) {

            foreach ($this->annotationReader->getMethodAnnotations($reflMethod) as $annotation) {
                if (defined(get_class($annotation) . '::event')) {

                    // Raise annotation event
                    if ($eventManager->hasListeners($annotation::event)) {
                        $eventManager->dispatchEvent(
                            $annotation::event,
                            new AnnotationEventArgs($target, EventType::method, $annotation, $reflMethod, $eventManager)
                        );
                    }
                }
            }
        }
    }
}
