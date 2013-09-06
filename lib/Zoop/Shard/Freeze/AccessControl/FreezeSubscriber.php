<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze\AccessControl;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Zoop\Shard\AccessControl\AbstractAccessControlSubscriber;
use Zoop\Shard\AccessControl\EventArgs as AccessControlEventArgs;
use Zoop\Shard\Freeze\Events;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class FreezeSubscriber extends AbstractAccessControlSubscriber
{

    protected $freezer;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::PRE_FREEZE,
            Events::PRE_THAW
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function preFreeze(LifecycleEventArgs $eventArgs)
    {
        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();

        if (! $accessController->areAllowed([Actions::FREEZE], null, $document)->getAllowed()) {
            //stop freeze
            $this->getFreezer()->thaw($document);

            $eventArgs->getDocumentManager()->getEventManager()->dispatchEvent(
                Events::FREEZE_DENIED,
                new AccessControlEventArgs($document, Actions::FREEZE)
            );
        }
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function preThaw(LifecycleEventArgs $eventArgs)
    {

        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();

        if (! $accessController->areAllowed([Actions::THAW], null, $document)->getAllowed()) {
            //stop thaw
            $this->getFreezer()->freeze($document);

            $eventArgs->getDocumentManager()->getEventManager()->dispatchEvent(
                Events::THAW_DENIED,
                new AccessControlEventArgs($document, Actions::THAW)
            );
        }
    }

    protected function getFreezer()
    {
        if (! isset($this->freezer)) {
            $this->freezer = $this->serviceLocator->get('freezer');
        }
        return $this->freezer;
    }
}
