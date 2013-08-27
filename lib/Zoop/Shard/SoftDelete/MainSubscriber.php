<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Zoop\Shard\SoftDelete\Events as SoftDeleteEvents;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Emits soft delete events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    protected $softDeleter;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            ODMEvents::onFlush
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
        $softDeleter = $this->getSoftDeleter();

        foreach ($unitOfWork->getScheduledDocumentUpdates() as $document) {

            $metadata = $documentManager->getClassMetadata(get_class($document));
            if (! isset($metadata->softDelete) || ! ($field = $metadata->softDelete['flag'])) {
                continue;
            }

            $eventManager = $documentManager->getEventManager();
            $changeSet = $unitOfWork->getDocumentChangeSet($document);

            if (!isset($changeSet[$field])) {
                if ($softDeleter->isSoftDeleted($document)) {
                    // Updates to softDeleted documents are not allowed. Roll them back
                    $unitOfWork->clearDocumentChangeSet(spl_object_hash($document));

                    // Raise softDeletedUpdateDenied
                    if ($eventManager->hasListeners(SoftDeleteEvents::SOFT_DELETED_UPDATE_DENIED)) {
                        $eventManager->dispatchEvent(
                            SoftDeleteEvents::SOFT_DELETED_UPDATE_DENIED,
                            new LifecycleEventArgs($document, $documentManager)
                        );
                    }
                    continue;
                } else {
                    continue;
                }
            }

            if ($changeSet[$field][1]) {
                // Trigger soft delete events

                // Raise preSoftDelete
                if ($eventManager->hasListeners(SoftDeleteEvents::PRE_SOFT_DELETE)) {
                    $eventManager->dispatchEvent(
                        SoftDeleteEvents::PRE_SOFT_DELETE,
                        new LifecycleEventArgs($document, $documentManager)
                    );
                }

                if ($softDeleter->isSoftDeleted($document)) {
                    // Raise postSoftDelete
                    if ($eventManager->hasListeners(SoftDeleteEvents::POST_SOFT_DELETE)) {
                        $eventManager->dispatchEvent(
                            SoftDeleteEvents::POST_SOFT_DELETE,
                            new LifecycleEventArgs($document, $documentManager)
                        );
                    }
                } else {
                    // Soft delete has been rolled back
                    $metadata = $documentManager->getClassMetadata(get_class($document));
                    $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
                }

            } else {
                // Trigger restore events

                // Raise preRestore
                if ($eventManager->hasListeners(SoftDeleteEvents::PRE_RESTORE)) {
                    $eventManager->dispatchEvent(
                        SoftDeleteEvents::PRE_RESTORE,
                        new LifecycleEventArgs($document, $documentManager)
                    );
                }

                if (! $softDeleter->isSoftDeleted($document)) {
                    // Raise postRestore
                    if ($eventManager->hasListeners(SoftDeleteEvents::POST_RESTORE)) {
                        $eventManager->dispatchEvent(
                            SoftDeleteEvents::POST_RESTORE,
                            new LifecycleEventArgs($document, $documentManager)
                        );
                    }
                } else {
                    // Restore has been rolled back
                    $metadata = $documentManager->getClassMetadata(get_class($document));
                    $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
                }
            }
        }
    }

    protected function getSoftDeleter()
    {
        if (! isset($this->softDeleter)) {
            $this->softDeleter = $this->serviceLocator->get('softDeleter');
        }
        return $this->softDeleter;
    }
}
