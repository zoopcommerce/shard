<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Zone;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;

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
    public function getSubscribedEvents(){
        return [
            Shard\Zones::event
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\EventArgs $eventArgs
     */
    public function annotationZones(AnnotationEventArgs $eventArgs)
    {
        $eventArgs->getMetadata()->zones = $eventArgs->getReflection()->getName();
    }
}