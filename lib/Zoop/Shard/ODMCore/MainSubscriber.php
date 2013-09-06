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
            $createEventArgs = new CoreEventArgs($document, CoreEventArgs::CREATE);
            $eventManager->dispatchEvent(Events::CREATE, $createEventArgs);
            if ($createEventArgs->getShortCircut()) {
                continue;
            }
            $eventManager->dispatchEvent(Events::VALIDATE, $createEventArgs);
            if ($createEventArgs->getShortCircut()) {
                continue;
            }
            $eventManager->dispatchEvent(Events::CRYPT, $createEventArgs);
        }

        foreach ($unitOfWork->getScheduledDocumentUpdates() as $document) {
            $updateEventArgs = new CoreEventArgs($document, CoreEventArgs::UPDATE);
            $eventManager->dispatchEvent(Events::UPDATE, $updateEventArgs);
            if ($updateEventArgs->getShortCircut()) {
                continue;
            }
            $eventManager->dispatchEvent(Events::VALIDATE, $updateEventArgs);
            if ($updateEventArgs->getShortCircut()) {
                continue;
            }
            $eventManager->dispatchEvent(Events::CRYPT, $updateEventArgs);
        }

        foreach ($unitOfWork->getScheduledDocumentDeletions() as $document) {
            $eventManager->dispatchEvent(Events::DELETE, new CoreEventArgs($document, CoreEventArgs::DELETE));
        }
    }
}
