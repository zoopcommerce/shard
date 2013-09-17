<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Doctrine\Common\EventArgs as BaseEventArgs;
use Doctrine\Common\EventManager as BaseEventManager;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class BootstrappedEventArgs extends BaseEventArgs
{
    protected $objectManager;

    protected $eventManager;

    public function getObjectManager() {
        return $this->objectManager;
    }

    public function getEventManager() {
        return $this->eventManager;
    }

    public function __construct(ObjectManager $objectManager, BaseEventManager $eventManager)
    {
        $this->objectManager = $objectManager;
        $this->eventManager = $eventManager;
    }
}
