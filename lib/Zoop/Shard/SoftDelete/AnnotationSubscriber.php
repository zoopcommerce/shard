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
    public function getSubscribedEvents()
    {
        return [
            Shard\SoftDelete::EVENT,
            Shard\SoftDelete\DeletedBy::EVENT,
            Shard\SoftDelete\DeletedOn::EVENT,
            Shard\SoftDelete\RestoredBy::EVENT,
            Shard\SoftDelete\RestoredOn::EVENT,
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

        $softDeleteMetadata = $this->getSoftDeleteMetadata($metadata);
        $softDeleteMetadata['flag'] = $field;
        $metadata->setSoftDelete($softDeleteMetadata);

        //Add sythentic annotation to create extra permission that will allow
        //updates on the softDelete field when access control is enabled.
        $permissionAnnotation = new Shard\Permission\Basic(
            [
                'roles' => BasicPermission::WILD,
                'allow' => Actions::update($field)
            ]
        );

        // Raise annotation event
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

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSoftDeleteDeletedBy(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $softDeleteMetadata = $this->getSoftDeleteMetadata($metadata);
        $softDeleteMetadata['deletedBy'] = $eventArgs->getReflection()->getName();
        $metadata->setSoftDelete($softDeleteMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSoftDeleteDeletedOn(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $softDeleteMetadata = $this->getSoftDeleteMetadata($metadata);
        $softDeleteMetadata['deletedOn'] = $eventArgs->getReflection()->getName();
        $metadata->setSoftDelete($softDeleteMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSoftDeleteRestoredBy(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $softDeleteMetadata = $this->getSoftDeleteMetadata($metadata);
        $softDeleteMetadata['restoredBy'] = $eventArgs->getReflection()->getName();
        $metadata->setSoftDelete($softDeleteMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSoftDeleteRestoredOn(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $softDeleteMetadata = $this->getSoftDeleteMetadata($metadata);
        $softDeleteMetadata['restoredOn'] = $eventArgs->getReflection()->getName();
        $metadata->setSoftDelete($softDeleteMetadata);
    }

    protected function getSoftDeleteMetadata($metadata)
    {
        if (!$metadata->hasProperty('softDelete')) {
            $metadata->addProperty('softDelete', true);
            $metadata->setSoftDelete([]);
        }

        return $metadata->getSoftDelete();
    }
}
