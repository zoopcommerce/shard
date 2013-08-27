<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State;

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
        return array(
            Shard\State::EVENT,
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationState(AnnotationEventArgs $eventArgs)
    {
        $field = $eventArgs->getReflection()->getName();
        $metadata = $eventArgs->getMetadata();
        $eventManager = $eventArgs->getEventManager();

        $metadata->state = [$field => $eventArgs->getAnnotation()->value];

        //Add sythentic annotation to create extra permission that will allow
        //updates on the state field when access control is enabled.
        $permissionAnnotation = new Shard\Permission\Basic(
            [
                'roles' => BasicPermission::WILD,
                'allow' => Actions::update($field)
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
}
