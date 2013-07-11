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
    public function getSubscribedEvents(){
        return [
            ODMEvents::onFlush
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs  $eventArgs)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();
        $eventManager = $documentManager->getEventManager();
        $freezer = $this->getFreezer();

        foreach ($unitOfWork->getScheduledDocumentUpdates() AS $document) {

            $metadata = $documentManager->getClassMetadata(get_class($document));
            if ( !isset($metadata->freeze) || ! ($field = $metadata->freeze['flag'])){
                continue;
            }

            $changeSet = $unitOfWork->getDocumentChangeSet($document);

            if (!isset($changeSet[$field])) {
                if ($freezer->isFrozen($document)) {
                    // Updates to frozen documents are not allowed. Roll them back
                    $unitOfWork->clearDocumentChangeSet(spl_object_hash($document));

                    // Raise frozenUpdateDenied
                    if ($eventManager->hasListeners(Events::frozenUpdateDenied)) {
                        $eventManager->dispatchEvent(
                            Events::frozenUpdateDenied,
                            new AccessControlEventArgs($document, $documentManager, 'update')
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
                if ($eventManager->hasListeners(Events::preFreeze)) {
                    $eventManager->dispatchEvent(
                        Events::preFreeze,
                        new LifecycleEventArgs($document, $documentManager)
                    );
                }

                if($freezer->isFrozen($document)){
                    // Raise postFreeze
                    if ($eventManager->hasListeners(Events::postFreeze)) {
                        $eventManager->dispatchEvent(
                            Events::postFreeze,
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
                if ($eventManager->hasListeners(Events::preThaw)) {
                    $eventManager->dispatchEvent(
                        Events::preThaw,
                        new LifecycleEventArgs($document, $documentManager)
                    );
                }

                if(!$freezer->isFrozen($document)){
                    // Raise postThaw
                    if ($eventManager->hasListeners(Events::postThaw)) {
                        $eventManager->dispatchEvent(
                            Events::postThaw,
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

        foreach ($unitOfWork->getScheduledDocumentDeletions() AS $document) {

            $metadata = $documentManager->getClassMetadata(get_class($document));
            if ( !isset($metadata->freeze) || ! ($field = $metadata->freeze['flag']) || ! $freezer->isFrozen($document)){
                continue;
            }

            // Deletions of frozen documents are not allowed. Roll them back
            $documentManager->persist($document);

            // Raise frozenDeleteDenied
            if ($eventManager->hasListeners(Events::frozenDeleteDenied)) {
                $eventManager->dispatchEvent(
                    Events::frozenDeleteDenied,
                    new AccessControlEventArgs($document, $documentManager, 'delete')
                );
            }
        }
    }

    protected function getFreezer(){
        if (!isset($this->freezer)){
            $this->freezer = $this->serviceLocator->get('freezer');
        }
        return $this->freezer;
    }
}