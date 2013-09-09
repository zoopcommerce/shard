<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\AccessControl\EventArgs as AccessControlEventArgs;
use Zoop\Shard\ODMCore\Events as ODMCoreEvents;
use Zoop\Shard\ODMCore\UpdateEventArgs;

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
        return [ODMCoreEvents::UPDATE];
    }

    public function update(UpdateEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $softDeleter = $this->getSoftDeleter();
        $metadata = $eventArgs->getMetadata();

        if (! ($field = $softDeleter->getSoftDeleteField($metadata))) {
            return;
        }

        $changeSet = $eventArgs->getChangeSet();
        $eventManager = $eventArgs->getEventManager();

        if (!isset($changeSet[$field])) {
            if ($softDeleter->isSoftDeleted($document, $metadata)) {
                // Updates to softDeleted documents are not allowed. Roll them back
                $eventArgs->setReject(true);

                // Raise softDeletedUpdateDenied
                $eventManager->dispatchEvent(
                    Events::SOFT_DELETED_UPDATE_DENIED,
                    new AccessControlEventArgs($document, 'softDelete')
                );
                return;
            } else {
                return;
            }
        }

        if ($changeSet[$field][1]) {
            // Trigger soft delete events

            // Raise preSoftDelete
            $softDeleteEventArgs = new SoftDeleteEventArgs($document, $eventArgs->getMetadata(), $eventManager);
            $eventManager->dispatchEvent(Events::PRE_SOFT_DELETE, $softDeleteEventArgs);
            if ($softDeleteEventArgs->getReject()){
                $eventArgs->setReject(true);
                return;
            }

            if ($softDeleter->isSoftDeleted($document, $metadata)) {
                // Raise postSoftDelete
                $eventManager->dispatchEvent(Events::POST_SOFT_DELETE, $softDeleteEventArgs);
                $eventArgs->setRecompute($softDeleteEventArgs->getRecompute());
            } else {
                // Soft delete has been rolled back
                unset($changeSet[$field]);
                $eventArgs->setRecompute(true);
            }

        } else {
            // Trigger restore events

            // Raise preRestore
            $softDeleteEventArgs = new SoftDeleteEventArgs($document, $metadata, $eventManager);
            $eventManager->dispatchEvent(Events::PRE_RESTORE, $softDeleteEventArgs);
            if ($softDeleteEventArgs->getReject()){
                $eventArgs->setReject(true);
                return;
            }

            if (! $softDeleter->isSoftDeleted($document, $metadata)) {
                // Raise postRestore
                $eventManager->dispatchEvent(Events::POST_RESTORE, $softDeleteEventArgs);
                $eventArgs->setRecompute($softDeleteEventArgs->getRecompute());
            } else {
                // Restore has been rolled back
                unset($changeSet[$field]);
                $eventArgs->setRecompute(true);
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
