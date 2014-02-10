<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Common\State\Transition;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\UpdateEventArgs;
use Zoop\Shard\Core\CreateEventArgs;
use Zoop\Shard\Core\ReadEventArgs;

/**
 * Emits soft delete events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            CoreEvents::READ,
            CoreEvents::CREATE,
            CoreEvents::UPDATE,
        ];
    }

    public function read(ReadEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();

        if (! ($stateMetadata = $metadata->getState())) {
            return;
        }

        $extension = $this->serviceLocator->get('extension.state');
        $include = $extension->getReadFilterInclude();
        $exclude = $extension->getReadFilterExclude();
        $field = array_keys($stateMetadata)[0];
        $criteria = [];

        if (count($include) > 0) {
            $criteria[$field] = ['$in' => $include];
        }

        if (count($exclude) > 0) {
            $criteria[$field] = ['$nin' => $exclude];
        }

        if (count($criteria) == 0) {
            return;
        }

        $eventArgs->addCriteria($criteria);
    }

    public function update(UpdateEventArgs $eventArgs)
    {
        if ($eventArgs->getReject()) {
            return;
        }

        $metadata = $eventArgs->getMetadata();
        if (! ($stateMetadata = $metadata->getState())) {
            return;
        }

        $document = $eventArgs->getDocument();
        $eventManager = $eventArgs->getEventManager();
        $changeSet = $eventArgs->getChangeSet();
        $field = array_keys($stateMetadata)[0];

        list($fromState, $toState) = $changeSet->getField($field);

        //stop state change if the new state is not on the defined state list
        if (count($stateMetadata[$field]) > 0 && ! in_array($toState, $stateMetadata[$field])) {
            $metadata->setFieldValue($document, $field, $fromState);
            $eventManager->dispatchEvent(Events::BAD_STATE, $eventArgs);
            $eventArgs->setReject(true);
            return;
        }

        // Raise preTransition
        $transitionEventArgs = new TransitionEventArgs(
            $document,
            $metadata,
            new Transition($fromState, $toState),
            $changeSet,
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
        if (! ($stateMetadata = $metadata->getState())) {
            return;
        }

        $field = array_keys($stateMetadata)[0];
        $document = $eventArgs->getDocument();

        if (count($stateMetadata[$field]) > 0 &&
            ! in_array($metadata->getFieldValue($document, $field), $stateMetadata[$field])
        ) {
            $eventArgs->getEventManager()->dispatchEvent(Events::BAD_STATE, $eventArgs);
            $eventArgs->setReject(true);
        }
    }
}
