<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze\AccessControl;

use Zoop\Shard\AccessControl\AbstractAccessControlSubscriber;
use Zoop\Shard\AccessControl\EventArgs as AccessControlEventArgs;
use Zoop\Shard\Freeze\Events;
use Zoop\Shard\Freeze\FreezerEventArgs;

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
     * @param  \Zoop\Shard\Freeze\FreezerEventArgs $eventArgs
     * @return type
     */
    public function preFreeze(FreezerEventArgs $eventArgs)
    {
        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (! $accessController->areAllowed([Actions::FREEZE], $metadata, $document)->getAllowed()) {
            //stop freeze
            $this->getFreezer()->thaw($document, $metadata);

            $eventArgs->setReject(true);

            $eventArgs->getEventManager()->dispatchEvent(
                Events::FREEZE_DENIED,
                new AccessControlEventArgs($document, Actions::FREEZE)
            );
        }
    }

    /**
     *
     * @param  \Zoop\Shard\Freeze\FreezerEventArgs $eventArgs
     * @return type
     */
    public function preThaw(FreezerEventArgs $eventArgs)
    {

        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (! $accessController->areAllowed([Actions::THAW], $metadata, $document)->getAllowed()) {
            //stop thaw
            $this->getFreezer()->freeze($document, $metadata);

            $eventArgs->setReject(true);

            $eventArgs->getEventManager()->dispatchEvent(
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
