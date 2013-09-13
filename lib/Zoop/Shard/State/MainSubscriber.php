<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State;

use Doctrine\Common\EventSubscriber;
use Zoop\Common\State\Transition;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\UpdateEventArgs;
use Zoop\Shard\Core\CreateEventArgs;
use Zoop\Shard\Core\MetadataSleepEventArgs;

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
    public function getSubscribedEvents()
    {
        return [
            CoreEvents::CREATE,
            CoreEvents::UPDATE,
            CoreEvents::METADATA_SLEEP,
        ];
    }

    public function update(UpdateEventArgs $eventArgs)
    {
        if ($eventArgs->getReject()) {
            return;
        }

        $metadata = $eventArgs->getMetadata();
        if (! isset($metadata->state)) {
            return;
        }

        $document = $eventArgs->getDocument();
        $eventManager = $eventArgs->getEventManager();
        $changeSet = $eventArgs->getChangeSet();
        $field = array_keys($metadata->state)[0];

        $fromState = $changeSet[$field][0];
        $toState = $changeSet[$field][1];

        //stop state change if the new state is not on the defined state list
        if (count($metadata->state[$field]) > 0 && ! in_array($toState, $metadata->state[$field])) {
            $metadata->setFieldValue($document, $field, $fromState);
            $eventArgs->setReject(true);
            $eventManager->dispatchEvent(Events::BAD_STATE, $eventArgs);

            return;
        }

        // Raise preTransition
        $transitionEventArgs = new TransitionEventArgs(
            $document,
            $metadata,
            new Transition($fromState, $toState),
            $eventManager
        );
        $eventManager->dispatchEvent(Events::PRE_TRANSITION, $transitionEventArgs);

        if ($transitionEventArgs->getReject()) {
            $eventArgs->setReject(true);

            return;
        }

        // Raise postTransition
        $eventManager->dispatchEvent(Events::POST_TRANSITION, $transitionEventArgs);
        if ($transitionEventArgs->getReject()) {
            $eventArgs->setReject(true);

            return;
        }
    }

    public function create(CreateEventArgs $eventArgs)
    {
        if ($eventArgs->getReject()) {
            return;
        }

        $metadata = $eventArgs->getMetadata();
        if (! isset($metadata->state)) {
            return;
        }

        $field = array_keys($metadata->state)[0];
        $document = $eventArgs->getDocument();

        if (count($metadata->state[$field]) > 0 &&
            ! in_array($metadata->getFieldValue($document, $field), $metadata->state[$field])
        ) {
            $eventArgs->setReject(true);
            $eventArgs->getEventManager()->dispatchEvent(
                Events::BAD_STATE,
                $eventArgs
            );
        }
    }

    public function metadataSleep(MetadataSleepEventArgs $eventArgs)
    {
        if (isset($eventArgs->getMetadata()->state)) {
            $eventArgs->addSerialized('state');
        }
    }
}
