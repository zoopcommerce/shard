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
     * @param  \Zoop\Shard\SoftDelete\SoftDeleteEventArgs $eventArgs
     * @return type
     */
    public function preSoftDelete(SoftDeleteEventArgs $eventArgs)
    {
        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (! $accessController->areAllowed([Actions::SOFT_DELETE], $metadata, $document)->getAllowed()) {
            //stop SoftDelete
            $this->getSoftDeleter()->restore($document, $metadata);

            $eventArgs->getEventManager()->dispatchEvent(
                Events::SOFT_DELETE_DENIED,
                $eventArgs
            );

            $eventArgs->setReject(true);
        }
    }

    /**
     *
     * @param  \Zoop\Shard\SoftDelete\SoftDeleteEventArgs $eventArgs
     * @return type
     */
    public function preRestore(SoftDeleteEventArgs $eventArgs)
    {
        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (! $accessController->areAllowed([Actions::RESTORE], $metadata, $document)->getAllowed()) {
            //stop restore
            $this->getSoftDeleter()->softDelete($document, $metadata);

            $eventArgs->getEventManager()->dispatchEvent(
                Events::RESTORE_DENIED,
                $eventArgs
            );

            $eventArgs->setReject(true);
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
