<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete\AccessControl;

use Zoop\Shard\AccessControl\AbstractAccessControlSubscriber;
use Zoop\Shard\SoftDelete\Events;
use Zoop\Shard\SoftDelete\SoftDeleteEventArgs;

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
    public function preSoftDelete(SoftDeleteEventArgs $eventArgs)
    {
        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();

        if (! $accessController->areAllowed([Actions::SOFT_DELETE], null, $document)->getAllowed()) {
            //stop SoftDelete
            $this->getSoftDeleter()->restore($document, $eventArgs->getMetadata());

            $eventArgs->setReject(true);

            $eventArgs->getEventManager()->dispatchEvent(
                Events::SOFT_DELETE_DENIED,
                $eventArgs
            );
        }
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function preRestore(SoftDeleteEventArgs $eventArgs)
    {
        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();

        if (! $accessController->areAllowed([Actions::RESTORE], null, $document)->getAllowed()) {
            //stop restore
            $this->getSoftDeleter()->softDelete($document, $eventArgs->getMetadata());

            $eventArgs->setReject(true);

            $eventArgs->getEventManager()->dispatchEvent(
                Events::RESTORE_DENIED,
                $eventArgs
            );
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
