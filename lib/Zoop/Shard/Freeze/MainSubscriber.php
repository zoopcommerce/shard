<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Zoop\Shard\AccessControl\EventArgs as AccessControlEventArgs;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Emits freeze events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $freezer;

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
        $eventManager = $documentManager->getEventManager();
        $freezer = $this->getFreezer();

        foreach ($unitOfWork->getScheduledDocumentUpdates() as $document) {

            $metadata = $documentManager->getClassMetadata(get_class($document));
            if (! isset($metadata->freeze) || ! ($field = $metadata->freeze['flag'])) {
                continue;
            }

            $changeSet = $unitOfWork->getDocumentChangeSet($document);

            if (!isset($changeSet[$field])) {
                if ($freezer->isFrozen($document)) {
                    // Updates to frozen documents are not allowed. Roll them back
                    $unitOfWork->clearDocumentChangeSet(spl_object_hash($document));

                    // Raise frozenUpdateDenied
                    if ($eventManager->hasListeners(Events::FROZEN_UPDATE_DENIED)) {
                        $eventManager->dispatchEvent(
                            Events::FROZEN_UPDATE_DENIED,
                            new AccessControlEventArgs($document, 'update')
                        );
                    }
                    continue;
                } else {
                    continue;
                }
            }

            if ($changeSet[$field][1]) {
                // Trigger freeze events

                // Raise preFreeze
                if ($eventManager->hasListeners(Events::PRE_FREEZE)) {
                    $eventManager->dispatchEvent(
                        Events::PRE_FREEZE,
                        new LifecycleEventArgs($document, $documentManager)
                    );
                }

                if ($freezer->isFrozen($document)) {
                    // Raise postFreeze
                    if ($eventManager->hasListeners(Events::POST_FREEZE)) {
                        $eventManager->dispatchEvent(
                            Events::POST_FREEZE,
                            new LifecycleEventArgs($document, $documentManager)
                        );
                    }
                } else {
                    // Freeze has been rolled back
                    $metadata = $documentManager->getClassMetadata(get_class($document));
                    $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
                }

            } else {
                // Trigger thaw events

                // Raise preThaw
                if ($eventManager->hasListeners(Events::PRE_THAW)) {
                    $eventManager->dispatchEvent(
                        Events::PRE_THAW,
                        new LifecycleEventArgs($document, $documentManager)
                    );
                }

                if (! $freezer->isFrozen($document)) {
                    // Raise postThaw
                    if ($eventManager->hasListeners(Events::POST_THAW)) {
                        $eventManager->dispatchEvent(
                            Events::POST_THAW,
                            new LifecycleEventArgs($document, $documentManager)
                        );
                    }
                } else {
                    // Thaw has been rolled back
                    $metadata = $documentManager->getClassMetadata(get_class($document));
                    $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
                }
            }
        }

        foreach ($unitOfWork->getScheduledDocumentDeletions() as $document) {

            $metadata = $documentManager->getClassMetadata(get_class($document));
            if (! isset($metadata->freeze) ||
                ! ($field = $metadata->freeze['flag']) || ! $freezer->isFrozen($document)
            ) {
                continue;
            }

            // Deletions of frozen documents are not allowed. Roll them back
            $documentManager->persist($document);

            // Raise frozenDeleteDenied
            if ($eventManager->hasListeners(Events::FROZEN_DELETE_DENIED)) {
                $eventManager->dispatchEvent(
                    Events::FROZEN_DELETE_DENIED,
                    new AccessControlEventArgs($document, 'delete')
                );
            }
        }
    }

    protected function getFreezer()
    {
        if (! isset($this->freezer)) {
            $this->freezer = $this->serviceLocator->get('freezer');
        }
        return $this->freezer;
    }
}
