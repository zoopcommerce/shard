<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\AccessControl\Actions;
use Zoop\Shard\AccessControl\BasicPermission;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;
use Zoop\Shard\Annotation\EventType;

/**
 * Emits freeze events
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
    public function getSubscribedEvents(){
        return [
            Shard\Freeze::event,
            Shard\Freeze\FrozenBy::event,
            Shard\Freeze\FrozenOn::event,
            Shard\Freeze\ThawedBy::event,
            Shard\Freeze\ThawedOn::event,
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationFreeze(AnnotationEventArgs $eventArgs)
    {
        $field = $eventArgs->getReflection()->getName();
        $metadata = $eventArgs->getMetadata();
        $eventManager = $eventArgs->getEventManager();

        $metadata->freeze['flag'] = $field;

        //Add sythentic annotation to create extra permission that will allow
        //updates on the freeze field when access control is enabled.
        $permissionAnnotation = new Shard\Permission\Basic([
            'roles' => BasicPermission::wild,
            'allow' => Actions::update($field)
        ]);

        // Raise annotation event
        if ($eventManager->hasListeners($permissionAnnotation::event)) {
            $eventManager->dispatchEvent(
                $permissionAnnotation::event,
                new AnnotationEventArgs($metadata, EventType::document, $permissionAnnotation, $metadata->getReflectionClass(), $eventManager)
            );
        }
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationFreezeFrozenBy(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->freeze['frozenBy'] = $eventArgs->getReflection()->getName();
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationFreezeFrozenOn(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->freeze['frozenOn'] = $eventArgs->getReflection()->getName();
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationFreezeThawedBy(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->freeze['thawedBy'] = $eventArgs->getReflection()->getName();
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationFreezeThawedOn(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->freeze['thawedOn'] = $eventArgs->getReflection()->getName();
    }
}