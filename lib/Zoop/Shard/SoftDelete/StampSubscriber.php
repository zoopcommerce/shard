<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Zoop\Shard\Stamp\AbstractStampSubscriber;

/**
 * Adds create and update stamps during persist
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class StampSubscriber extends AbstractStampSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::POST_SOFT_DELETE,
            Events::POST_RESTORE
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $eventArgs
     */
    public function postSoftDelete(LifecycleEventArgs $eventArgs)
    {
        $recomputeChangeSet = false;
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getDocumentManager()->getClassMetadata(get_class($document));

        if (isset($metadata->softDelete['deletedBy'])) {
            $metadata->reflFields[$metadata->softDelete['deletedBy']]->setValue($document, $this->getUsername());
            $recomputeChangeSet = true;
        }
        if (isset($metadata->softDelete['deletedOn'])) {
            $metadata->reflFields[$metadata->softDelete['deletedOn']]->setValue($document, time());
            $recomputeChangeSet = true;
        }
        if ($recomputeChangeSet) {
            $this->recomputeChangeset($eventArgs);
        }
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $eventArgs
     */
    public function postRestore(LifecycleEventArgs $eventArgs)
    {
        $recomputeChangeSet = false;
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getDocumentManager()->getClassMetadata(get_class($document));

        if (isset($metadata->softDelete['restoredBy'])) {
            $metadata->reflFields[$metadata->softDelete['restoredBy']]->setValue($document, $this->getUsername());
            $recomputeChangeSet = true;
        }
        if (isset($metadata->softDelete['restoredOn'])) {
            $metadata->reflFields[$metadata->softDelete['restoredOn']]->setValue($document, time());
            $recomputeChangeSet = true;
        }
        if ($recomputeChangeSet) {
            $this->recomputeChangeset($eventArgs);
        }
    }
}
