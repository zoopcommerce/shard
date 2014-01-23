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
use Zoop\Shard\Core\AbstractChangeEventArgs;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\ChangeSet;
use Zoop\Shard\Core\CreateEventArgs;
use Zoop\Shard\Core\DeleteEventArgs;
use Zoop\Shard\Core\UpdateEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class FlushSubscriber implements EventSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            ODMEvents::onFlush,
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
        $metadata = $documentManager->getClassMetadata(get_class($document));

        $createEventArgs = new CreateEventArgs(
            $document,
            $metadata,
            new ChangeSet($documentManager->getUnitOfWork()->getDocumentChangeSet($document)),
            $eventManager
        );

        $events = [
            CoreEvents::CREATE,
            CoreEvents::VALIDATE,
            CoreEvents::CRYPT
        ];

        foreach ($events as $event) {
            $eventManager->dispatchEvent($event, $createEventArgs);
            if ($createEventArgs->getReject()) {
                $this->rejectCreate($document, $documentManager);

                return;
            }
            if (count($createEventArgs->getRecompute()) > 0) {
                $this->recompute($document, $createEventArgs, $documentManager);
                $createEventArgs = new CreateEventArgs(
                    $document,
                    $metadata,
                    new ChangeSet($documentManager->getUnitOfWork()->getDocumentChangeSet($document)),
                    $eventManager
                );
            }
        }
    }

    protected function update($document, $documentManager)
    {
        $eventManager = $documentManager->getEventManager();
        $metadata = $documentManager->getClassMetadata(get_class($document));
        $changeSet = $documentManager->getUnitOfWork()->getDocumentChangeSet($document);
        if (count($changeSet) == 0) {
            return;
        }

        $updateEventArgs = new UpdateEventArgs(
            $document,
            $metadata,
            new ChangeSet($changeSet),
            $eventManager
        );

        $events = [
            CoreEvents::UPDATE,
            CoreEvents::VALIDATE,
            CoreEvents::CRYPT
        ];

        foreach ($events as $event) {
            $eventManager->dispatchEvent($event, $updateEventArgs);
            if ($updateEventArgs->getReject()) {
                $this->rejectUpdate($document, $documentManager);

                return;
            }
            if (count($updateEventArgs->getRecompute()) > 0) {
                $this->recompute($document, $updateEventArgs, $documentManager);
                $updateEventArgs = new UpdateEventArgs(
                    $document,
                    $metadata,
                    new ChangeSet($documentManager->getUnitOfWork()->getDocumentChangeSet($document)),
                    $eventManager
                );
            }
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
        $eventManager->dispatchEvent(CoreEvents::DELETE, $deleteEventArgs);
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

    protected function rejectUpdate($document, $documentManager)
    {
        //stop updates
        $unitOfWork = $documentManager->getUnitOfWork();
        $metadata = $documentManager->getClassMetadata(get_class($document));

        foreach ($unitOfWork->getDocumentChangeSet($document) as $field => $change) {
            $metadata->setFieldValue($document, $field, $change[0]);
        }
        $unitOfWork->clearDocumentChangeSet(spl_object_hash($document));
        $unitOfWork->persist($document);
    }

    protected function rejectDelete($document, $documentManager)
    {
        //stop delete
        $documentManager->persist($document);
    }

    protected function recompute($document, AbstractChangeEventArgs $eventArgs, $documentManager)
    {
        $unitOfWork = $documentManager->getUnitOfWork();
        $changeSet = $eventArgs->getChangeSet();
        $metadata = $eventArgs->getMetadata();

        foreach ($eventArgs->getRecompute() as $field) {
            if ($changeSet->hasField($field)) {
                $oldValue = $changeSet->getField($field)[0];
            } else {
                $oldValue = null;
            }
            $unitOfWork->propertyChanged(
                $document,
                $field,
                $oldValue,
                $metadata->getFieldValue($document, $field)
            );
        }
    }
}
