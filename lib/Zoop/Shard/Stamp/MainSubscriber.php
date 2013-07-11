<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Stamp;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;

/**
 * Adds create and update stamps during persist
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber extends AbstractStampSubscriber {

    /**
     *
     * @return array
     */
    public function getSubscribedEvents() {
        return [
            ODMEvents::prePersist,
            ODMEvents::preUpdate
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs) {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getDocumentManager()->getClassMetadata(get_class($document));

        if(isset($metadata->stamp['createdBy'])){
            $metadata->reflFields[$metadata->stamp['createdBy']]->setValue($document, $this->getUsername());
        }
        if(isset($metadata->stamp['createdOn'])){
            $metadata->reflFields[$metadata->stamp['createdOn']]->setValue($document, time());
        }
        if(isset($metadata->stamp['updatedBy'])){
            $metadata->reflFields[$metadata->stamp['updatedBy']]->setValue($document, $this->getUsername());
        }
        if(isset($metadata->stamp['updatedOn'])){
            $metadata->reflFields[$metadata->stamp['updatedOn']]->setValue($document, time());
        }
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs) {
        $recomputeChangeSet = false;
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getDocumentManager()->getClassMetadata(get_class($document));

        if(isset($metadata->stamp['updatedBy'])){
            $metadata->reflFields[$metadata->stamp['updatedBy']]->setValue($document, $this->getUsername());
            $recomputeChangeSet = true;
        }
        if(isset($metadata->stamp['updatedOn'])){
            $metadata->reflFields[$metadata->stamp['updatedOn']]->setValue($document, time());
            $recomputeChangeSet = true;
        }

        if ($recomputeChangeSet) {
            $this->recomputeChangeset($eventArgs);
        }
    }
}