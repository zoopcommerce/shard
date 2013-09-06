<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\Common\EventSubscriber;
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
            Shard\AccessControl::EVENT
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationAccessControl(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $metadata = $eventArgs->getMetadata();

        if ($annotation->value) {
            $metadata->permissions = [];
            $eventManager = $eventArgs->getEventManager();
            foreach ($annotation->value as $permissionAnnotation) {
                if (defined(get_class($permissionAnnotation) . '::EVENT')) {

                    // Raise annotation event
                    $eventManager->dispatchEvent(
                        $permissionAnnotation::EVENT,
                        new AnnotationEventArgs(
                            $metadata,
                            EventType::DOCUMENT,
                            $permissionAnnotation,
                            $eventArgs->getReflection(),
                            $eventManager
                        )
                    );
                }
            }
        }
    }
}
