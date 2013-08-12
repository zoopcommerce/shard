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

        foreach ($unitOfWork->getScheduledDocumentInsertions() as $document) {

            $metadata = $documentManager->getClassMetadata(get_class($document));
            if ( ! isset($metadata->state)){
                continue;
            }

            $field = array_keys($metadata->state)[0];

            if (count($metadata->state[$field]) > 0 && ! in_array($metadata->reflFields[$field]->getValue($document), $metadata->state[$field])){

                $unitOfWork->detach($document);
                $eventManager = $documentManager->getEventManager();
                if ($eventManager->hasListeners(Events::badState)) {
                    $eventManager->dispatchEvent(
                        Events::badState,
                        $eventArgs
                    );
                }
            }
        }

        foreach ($unitOfWork->getScheduledDocumentUpdates() AS $document) {

            $metadata = $documentManager->getClassMetadata(get_class($document));
            if ( ! isset($metadata->state)){
                continue;
            }

            $eventManager = $documentManager->getEventManager();
            $changeSet = $unitOfWork->getDocumentChangeSet($document);
            $field = array_keys($metadata->state)[0];

            if (!isset($changeSet[$field])) {
                continue;
            }

            $fromState = $changeSet[$field][0];
            $toState = $changeSet[$field][1];

            //stop state change if the new state is not on the defined state list
            if (count($metadata->state[$field]) > 0 && ! in_array($toState, $metadata->state[$field])){
                $metadata->reflFields[$field]->setValue($document, $fromState);
                if ($eventManager->hasListeners(Events::badState)) {
                    $eventManager->dispatchEvent(
                        Events::badState,
                        $eventArgs
                    );
                }
                $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
                continue;
            }

            // Raise preTransition
            if ($eventManager->hasListeners(Events::preTransition)) {
                $eventManager->dispatchEvent(
                    Events::preTransition,
                    new TransitionEventArgs(new Transition($fromState, $toState), $document, $documentManager)
                );
            }

            if ($document->getState() == $fromState){
                //State change has been rolled back
                $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
                continue;
            }

            // Raise onTransition
            if ($eventManager->hasListeners(Events::onTransition)) {
                $eventManager->dispatchEvent(
                    Events::onTransition,
                    new TransitionEventArgs(new Transition($fromState, $toState), $document, $documentManager)
                );
            }

            // Force change set update
            $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);

            // Raise postTransition - this is when workflow vars should be updated
            if ($eventManager->hasListeners(Events::postTransition)) {
                $eventManager->dispatchEvent(
                    Events::postTransition,
                    new TransitionEventArgs(new Transition($fromState, $toState), $document, $documentManager)
                );
            }
        }
    }
}