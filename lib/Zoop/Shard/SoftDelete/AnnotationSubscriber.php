<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\AccessControl\Actions;
use Zoop\Shard\AccessControl\BasicPermission;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;
use Zoop\Shard\Annotation\EventType;

/**
 * Emits soft delete events
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
            Shard\SoftDelete::event,
            Shard\SoftDelete\DeletedBy::event,
            Shard\SoftDelete\DeletedOn::event,
            Shard\SoftDelete\RestoredBy::event,
            Shard\SoftDelete\RestoredOn::event,
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSoftDelete(AnnotationEventArgs $eventArgs)
    {
        $field = $eventArgs->getReflection()->getName();
        $metadata = $eventArgs->getMetadata();
        $eventManager = $eventArgs->getEventManager();

        $metadata->softDelete['flag'] = $field;

        //Add sythentic annotation to create extra permission that will allow
        //updates on the softDelete field when access control is enabled.
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
    public function annotationSoftDeleteDeletedBy(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->softDelete['deletedBy'] = $eventArgs->getReflection()->getName();
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSoftDeleteDeletedOn(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->softDelete['deletedOn'] = $eventArgs->getReflection()->getName();
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSoftDeleteRestoredBy(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->softDelete['restoredBy'] = $eventArgs->getReflection()->getName();
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSoftDeleteRestoredOn(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->softDelete['restoredOn'] = $eventArgs->getReflection()->getName();
    }
}