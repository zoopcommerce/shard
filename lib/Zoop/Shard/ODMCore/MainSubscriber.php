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

/**
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

        foreach ($unitOfWork->getScheduledDocumentInsertions() as $document) {
            if ($eventManager->hasListeners(Events::CREATE)) {
                $eventManager->dispatchEvent(
                    Events::CREATE,
                    new CreateEventArgs($document)
                );
            }
        }

        foreach ($unitOfWork->getScheduledDocumentUpdates() as $document) {
            if ($eventManager->hasListeners(Events::UPDATE)) {
                $eventManager->dispatchEvent(
                    Events::UPDATE,
                    new UpdateEventArgs($document)
                );
            }
        }

        //Check delete permsisions
        foreach ($unitOfWork->getScheduledDocumentDeletions() as $document) {
            if ($eventManager->hasListeners(Events::DELETE)) {
                $eventManager->dispatchEvent(
                    Events::DELETE,
                    new DeleteEventArgs($document)
                );
            }
        }
    }
}
