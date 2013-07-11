<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Owner;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Zoop\Shard\Stamp\AbstractStampSubscriber;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber extends AbstractStampSubscriber {

    /**
     *
     * @return array
     */
    public function getSubscribedEvents(){
        return [
            ODMEvents::prePersist
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs) {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getDocumentManager()->getClassMetadata(get_class($document));

        if(isset($metadata->owner)){
            $reflField = $metadata->reflFields[$metadata->owner];
            $owner = $reflField->getValue($document);
            if (!isset($owner)){
                $reflField->setValue($document, $this->getUsername());
            }
        }
    }
}