<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\AccessControl\EventArgs as AccessControlEventArgs;
use Zoop\Shard\ODMCore\Events as ODMCoreEvents;
use Zoop\Shard\ODMCore\UpdateEventArgs;
use Zoop\Shard\ODMCore\DeleteEventArgs;

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
            ODMCoreEvents::DELETE,
            ODMCoreEvents::UPDATE,
        ];
    }

    public function update(UpdateEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $freezer = $this->getFreezer();
        $metadata = $eventArgs->getMetadata();

        if (! ($field = $freezer->getFreezeField($metadata))) {
            return;
        }

        $changeSet = $eventArgs->getChangeSet();
        $eventManager = $eventArgs->getEventManager();

        if (! isset($changeSet[$field])) {
            if ($freezer->isFrozen($document, $metadata)) {
                // Updates to frozen documents are not allowed. Roll them back
                $eventArgs->setReject(true);

                // Raise frozenUpdateDenied
                $eventManager->dispatchEvent(
                    Events::FROZEN_UPDATE_DENIED,
                    new AccessControlEventArgs($document, 'update')
                );
                return;
            } else {
                return;
            }
        }

        if ($changeSet[$field][1]) {
            // Trigger freeze events

            // Raise preFreeze
            $freezerEventArgs = new FreezerEventArgs($document, $eventArgs->getMetadata(), $eventManager);
            $eventManager->dispatchEvent(Events::PRE_FREEZE, $freezerEventArgs);
            if ($freezerEventArgs->getReject()){
                $eventArgs->setReject(true);
                return;
            }

            if ($freezer->isFrozen($document, $metadata)) {
                // Raise postFreeze
                $eventManager->dispatchEvent(Events::POST_FREEZE, $freezerEventArgs);
                $eventArgs->setRecompute($freezerEventArgs->getRecompute());
            } else {
                // Freeze has been rolled back
                unset($changeSet[$field]);
                $eventArgs->setRecompute(true);
            }

        } else {
            // Trigger thaw events

            // Raise preThaw
            $freezerEventArgs = new FreezerEventArgs($document, $metadata, $eventManager);
            $eventManager->dispatchEvent(Events::PRE_THAW, $freezerEventArgs);
            if ($freezerEventArgs->getReject()){
                $eventArgs->setReject(true);
                return;
            }

            if (! $freezer->isFrozen($document, $metadata)) {
                // Raise postThaw
                $eventManager->dispatchEvent(Events::POST_THAW, $freezerEventArgs);
                $eventArgs->setRecompute($freezerEventArgs->getRecompute());
            } else {
                // Thaw has been rolled back
                unset($changeSet[$field]);
                $eventArgs->setRecompute(true);
            }
        }
    }

    public function delete(DeleteEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();
        $freezer = $this->getFreezer();

        if ( !($field = $freezer->getFreezeField($metadata)) || ! $freezer->isFrozen($document, $metadata)) {
            return;
        }

        // Deletions of frozen documents are not allowed. Roll them back
        $eventArgs->setReject(true);

        // Raise frozenDeleteDenied
        $eventArgs->getEventManager()->dispatchEvent(
            Events::FROZEN_DELETE_DENIED,
            new AccessControlEventArgs($document, 'delete')
        );
    }

    protected function getFreezer()
    {
        if (! isset($this->freezer)) {
            $this->freezer = $this->serviceLocator->get('freezer');
        }
        return $this->freezer;
    }
}
