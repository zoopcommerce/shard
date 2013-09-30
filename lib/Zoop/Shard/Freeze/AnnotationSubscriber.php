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
    public function getSubscribedEvents()
    {
        return [
            Shard\Freeze::EVENT,
            Shard\Freeze\FrozenBy::EVENT,
            Shard\Freeze\FrozenOn::EVENT,
            Shard\Freeze\ThawedBy::EVENT,
            Shard\Freeze\ThawedOn::EVENT,
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

        $freezeMetadata = $this->getFreezeMetadata($metadata);
        $freezeMetadata['flag'] = $field;
        $metadata->setFreeze($freezeMetadata);

        //Add sythentic annotation to create extra permission that will allow
        //updates on the freeze field when access control is enabled.
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
    public function annotationFreezeFrozenBy(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $freezeMetadata = $this->getFreezeMetadata($metadata);
        $freezeMetadata['frozenBy'] = $eventArgs->getReflection()->getName();
        $metadata->setFreeze($freezeMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationFreezeFrozenOn(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $freezeMetadata = $this->getFreezeMetadata($metadata);
        $freezeMetadata['frozenOn'] = $eventArgs->getReflection()->getName();
        $metadata->setFreeze($freezeMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationFreezeThawedBy(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $freezeMetadata = $this->getFreezeMetadata($metadata);
        $freezeMetadata['thawedBy'] = $eventArgs->getReflection()->getName();
        $metadata->setFreeze($freezeMetadata);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationFreezeThawedOn(AnnotationEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $freezeMetadata = $this->getFreezeMetadata($metadata);
        $freezeMetadata['thawedOn'] = $eventArgs->getReflection()->getName();
        $metadata->setFreeze($freezeMetadata);
    }

    protected function getFreezeMetadata($metadata)
    {
        if (!$metadata->hasProperty('freeze')) {
            $metadata->addProperty('freeze', true);
            $metadata->setFreeze([]);
        }
        return $metadata->getFreeze();
    }
}
