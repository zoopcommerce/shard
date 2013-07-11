<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Zoop\Common\State\Transition;
use Zoop\Shard\State\Events as StateEvents;
use Zoop\Shard\State\EventArgs as TransitionEventArgs;

/**
 * Emits soft delete events
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
    public function getSubscribedEvents(){
        return array(
            ODMEvents::onFlush
        );
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs  $eventArgs)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledDocumentUpdates() AS $document) {

            $metadata = $documentManager->getClassMetadata(get_class($document));
            if ( ! isset($metadata->state)){
                continue;
            }

            $eventManager = $documentManager->getEventManager();
            $changeSet = $unitOfWork->getDocumentChangeSet($document);
            $field = $metadata->state;

            if (!isset($changeSet[$field])) {
                continue;
            }

            $fromState = $changeSet[$field][0];
            $toState = $changeSet[$field][1];

            // Raise preTransition
            if ($eventManager->hasListeners(StateEvents::preTransition)) {
                $eventManager->dispatchEvent(
                    StateEvents::preTransition,
                    new TransitionEventArgs(new Transition($fromState, $toState), $document, $documentManager)
                );
            }

            if ($document->getState() == $fromState){
                //State change has been rolled back
                $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
                continue;
            }

            // Raise onTransition
            if ($eventManager->hasListeners(StateEvents::onTransition)) {
                $eventManager->dispatchEvent(
                    StateEvents::onTransition,
                    new TransitionEventArgs(new Transition($fromState, $toState), $document, $documentManager)
                );
            }

            // Force change set update
            $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);

            // Raise postTransition - this is when workflow vars should be updated
            if ($eventManager->hasListeners(StateEvents::postTransition)) {
                $eventManager->dispatchEvent(
                    StateEvents::postTransition,
                    new TransitionEventArgs(new Transition($fromState, $toState), $document, $documentManager)
                );
            }
        }
    }
}