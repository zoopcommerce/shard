<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

trait EventManagerTrait
{

    protected $eventManager;

    public function getEventManager()
    {
        if (!isset($this->eventManager)) {
            $this->eventManager = $this->serviceLocator->get('eventmanager');
        }

        return $this->eventManager;
    }

    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
