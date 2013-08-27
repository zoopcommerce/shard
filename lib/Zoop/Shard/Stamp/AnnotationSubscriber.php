<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Stamp;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\AccessControl\Actions;
use Zoop\Shard\AccessControl\BasicPermission;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;
use Zoop\Shard\Annotation\EventType;

/**
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
            Shard\Stamp\CreatedBy::EVENT,
            Shard\Stamp\CreatedOn::EVENT,
            Shard\Stamp\UpdatedOn::EVENT,
            Shard\Stamp\UpdatedBy::EVENT,
        ];
    }

    public function annotationStampCreatedBy(AnnotationEventArgs $eventArgs)
    {
        $field = $eventArgs->getReflection()->getName();
        $metadata = $eventArgs->getMetadata();
        $eventManager = $eventArgs->getEventManager();

        $metadata->stamp['createdBy'] = $field;

        //Add sythentic annotation to create extra permission that will prevent
        //updates on the createdby field when access control is enabled.
        $permissionAnnotation = new Shard\Permission\Basic(
            [
                'roles' => BasicPermission::WILD,
                'deny' => Actions::update($field)
            ]
        );

        // Raise annotation event
        if ($eventManager->hasListeners($permissionAnnotation::EVENT)) {
            $eventManager->dispatchEvent(
                $permissionAnnotation::EVENT,
                new AnnotationEventArgs(
                    $metadata,
                    EventType::DOCUMENT,
                    $permissionAnnotation,
                    $metadata->getReflectionClass(),
                    $eventManager
                )
            );
        }
    }

    public function annotationStampCreatedOn(AnnotationEventArgs $eventArgs)
    {
        $field = $eventArgs->getReflection()->getName();
        $metadata = $eventArgs->getMetadata();
        $eventManager = $eventArgs->getEventManager();

        $metadata->stamp['createdOn'] = $field;

        //Add sythentic annotation to create extra permission that will prevent
        //updates on the createdby field when access control is enabled.
        $permissionAnnotation = new Shard\Permission\Basic(
            [
                'roles' => BasicPermission::WILD,
                'deny' => Actions::update($field)
            ]
        );

        // Raise annotation event
        if ($eventManager->hasListeners($permissionAnnotation::EVENT)) {
            $eventManager->dispatchEvent(
                $permissionAnnotation::EVENT,
                new AnnotationEventArgs(
                    $metadata,
                    EventType::DOCUMENT,
                    $permissionAnnotation,
                    $metadata->getReflectionClass(),
                    $eventManager
                )
            );
        }
    }

    public function annotationStampUpdatedBy(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        if (! isset($metadata->stamp)) {
            $metadata->stamp = [];
        }
        $metadata->stamp['updatedBy'] = $eventArgs->getReflection()->getName();
    }

    public function annotationStampUpdatedOn(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        if (! isset($metadata->stamp)) {
            $metadata->stamp = [];
        }
        $metadata->stamp['updatedOn'] = $eventArgs->getReflection()->getName();
    }
}
