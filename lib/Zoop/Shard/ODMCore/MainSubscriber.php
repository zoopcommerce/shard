<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber
{

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            ODMEvents::onFlush
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledDocumentInsertions() as $document) {
            $this->create($document, $documentManager);
        }

        foreach ($unitOfWork->getScheduledDocumentUpdates() as $document) {
            $this->update($document, $documentManager);
        }

        foreach ($unitOfWork->getScheduledDocumentDeletions() as $document) {
            $this->delete($document, $documentManager);
        }
    }

    protected function create($document, $documentManager)
    {
        $eventManager = $documentManager->getEventManager();

        $createEventArgs = new CreateEventArgs(
            $document,
            $documentManager->getClassMetadata(get_class($document)),
            $eventManager
        );
        $eventManager->dispatchEvent(Events::CREATE, $createEventArgs);
        if ($createEventArgs->getReject()) {
            $this->rejectCreate($document, $documentManager);
            return;
        }
        $eventManager->dispatchEvent(Events::VALIDATE, $createEventArgs);
        if ($createEventArgs->getReject()) {
            $this->rejectCreate($document, $documentManager);
            return;
        }
        $eventManager->dispatchEvent(Events::CRYPT, $createEventArgs);
        if ($createEventArgs->getReject()) {
            $this->rejectCreate($document, $documentManager);
        }
    }

    protected function update($document, $documentManager)
    {
        $unitOfWork = $documentManager->getUnitOfWork();
        $eventManager = $documentManager->getEventManager();

        $changeSet = $unitOfWork->getDocumentChangeSet($document);
        if (count($changeSet) == 0) {
            return;
        }
        $updateEventArgs = new UpdateEventArgs(
            $document,
            $documentManager->getClassMetadata(get_class($document)),
            $changeSet,
            $eventManager
        );
        $eventManager->dispatchEvent(Events::UPDATE, $updateEventArgs);
        if ($updateEventArgs->getReject()) {
            $this->rejectUpdate($document, $changeSet, $documentManager);
            return;
        }
        if ($updateEventArgs->getRecompute()) {
            $this->recomputeUpdate($document, $documentManager);
        }
        $eventManager->dispatchEvent(Events::VALIDATE, $updateEventArgs);
        if ($updateEventArgs->getReject()) {
            $this->rejectUpdate($document, $changeSet, $documentManager);
            return;
        }
        if ($updateEventArgs->getRecompute()) {
            $this->recomputeUpdate($document, $documentManager);
        }
        $eventManager->dispatchEvent(Events::CRYPT, $updateEventArgs);
        if ($updateEventArgs->getReject()) {
            $this->rejectUpdate($document, $changeSet, $documentManager);
        }
        if ($updateEventArgs->getRecompute()) {
            $this->recomputeUpdate($document, $documentManager);
        }
    }

    protected function delete($document, $documentManager)
    {
        $eventManager = $documentManager->getEventManager();

        $deleteEventArgs = new DeleteEventArgs(
            $document,
            $documentManager->getClassMetadata(get_class($document)),
            $eventManager
        );
        $eventManager->dispatchEvent(Events::DELETE, $deleteEventArgs);
        if ($deleteEventArgs->getReject()) {
            $this->rejectDelete($document, $documentManager);
        }
    }

    protected function rejectCreate($document, $documentManager)
    {
        //stop creation

        $unitOfWork = $documentManager->getUnitOfWork();
        $metadata = $documentManager->getClassMetadata(get_class($document));

        if ($metadata->isEmbeddedDocument) {
            list($mapping, $parent) = $unitOfWork->getParentAssociation($document);
            $parentMetadata = $documentManager->getClassMetadata(get_class($parent));
            if ($mapping['type'] == 'many') {
                $collection = $parentMetadata->reflFields[$mapping['fieldName']]->getValue($parent);
                $collection->removeElement($document);
                $unitOfWork->recomputeSingleDocumentChangeSet($parentMetadata, $parent);
            } else {
                $parentMetadata->reflFields[$mapping['fieldName']]->setValue($document, null);
            }
        }
        $unitOfWork->detach($document);
    }

    protected function rejectUpdate($document, $changeSet, $documentManager)
    {
        $unitOfWork = $documentManager->getUnitOfWork();
        $metadata = $documentManager->getClassMetadata(get_class($document));

        //roll back changes
        foreach ($changeSet as $field => $change) {
            $metadata->setFieldValue($document, $field, $change[0]);
        }

        //stop updates
        $unitOfWork->clearDocumentChangeSet(spl_object_hash($document));
    }

    protected function rejectDelete($document, $documentManager)
    {
        //stop delete
        $documentManager->persist($document);
    }

    protected function recomputeUpdate($document, $documentManager)
    {
        $documentManager->getUnitOfWork()->recomputeSingleDocumentChangeSet(
            $documentManager->getClassMetadata(get_class($document)),
            $document
        );
    }
}
