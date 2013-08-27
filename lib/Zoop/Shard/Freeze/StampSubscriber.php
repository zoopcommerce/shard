<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Zoop\Shard\Stamp\AbstractStampSubscriber;

/**
 * Adds freeze and thaw stamps during persist
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
            Events::POST_FREEZE,
            Events::POST_THAW
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $eventArgs
     */
    public function postFreeze(LifecycleEventArgs $eventArgs)
    {
        $recomputeChangeSet = false;
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getDocumentManager()->getClassMetadata(get_class($document));

        if (isset($metadata->freeze['frozenBy'])) {
            $metadata->reflFields[$metadata->freeze['frozenBy']]->setValue($document, $this->getUsername());
            $recomputeChangeSet = true;
        }
        if (isset($metadata->freeze['frozenOn'])) {
            $metadata->reflFields[$metadata->freeze['frozenOn']]->setValue($document, time());
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
    public function postThaw(LifecycleEventArgs $eventArgs)
    {
        $recomputeChangeSet = false;
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getDocumentManager()->getClassMetadata(get_class($document));

        if (isset($metadata->freeze['thawedBy'])) {
            $metadata->reflFields[$metadata->freeze['thawedBy']]->setValue($document, $this->getUsername());
            $recomputeChangeSet = true;
        }
        if (isset($metadata->freeze['thawedOn'])) {
            $metadata->reflFields[$metadata->freeze['thawedOn']]->setValue($document, time());
            $recomputeChangeSet = true;
        }
        if ($recomputeChangeSet) {
            $this->recomputeChangeset($eventArgs);
        }
    }
}
