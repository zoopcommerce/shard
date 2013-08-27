<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete\AccessControl;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Zoop\Shard\AccessControl\AbstractAccessControlSubscriber;
use Zoop\Shard\SoftDelete\Events;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class SoftDeleteSubscriber extends AbstractAccessControlSubscriber
{

    protected $softDeleter;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::PRE_SOFT_DELETE,
            Events::PRE_RESTORE
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function preSoftDelete(LifecycleEventArgs $eventArgs)
    {
        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();

        if (! $accessController->areAllowed([Actions::SOFT_DELETE], null, $document)->getAllowed()) {
            //stop SoftDelete
            $this->getSoftDeleter()->restore($document);

            $eventManager = $eventArgs->getDocumentManager()->getEventManager();
            if ($eventManager->hasListeners(Events::SOFT_DELETE_DENIED)) {
                $eventManager->dispatchEvent(
                    Events::SOFT_DELETE_DENIED,
                    $eventArgs
                );
            }
        }
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function preRestore(LifecycleEventArgs $eventArgs)
    {
        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();

        if (! $accessController->areAllowed([Actions::RESTORE], null, $document)->getAllowed()) {
            //stop restore
            $this->getSoftDeleter()->softDelete($document);

            $eventManager = $eventArgs->getDocumentManager()->getEventManager();
            if ($eventManager->hasListeners(Events::RESTORE_DENIED)) {
                $eventManager->dispatchEvent(
                    Events::RESTORE_DENIED,
                    $eventArgs
                );
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
