<?php

namespace Zoop\Shard\Core;

use Doctrine\Common\EventManager as BaseEventManager;
use Doctrine\Common\EventArgs;

class EventManager extends BaseEventManager
{

    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName The name of the event to dispatch. The name of the event is
     *                                  the name of the method that is invoked on listeners.
     * @param EventArgs|null $eventArgs The event arguments to pass to the event handlers/listeners.
     *                                  If not supplied, the single empty EventArgs instance is used.
     *
     * @return boolean
     */
    public function dispatchEvent($eventName, EventArgs $eventArgs = null)
    {
        if (! $this->hasListeners($eventName)) {
            return;
        }

        $eventArgs = $eventArgs === null ? EventArgs::getEmptyInstance() : $eventArgs;
        foreach ($this->getListeners($eventName) as $listener) {
            $listener->$eventName($eventArgs);
            if ($eventArgs instanceof RejectInterface && $eventArgs->getReject()) {
                break;
            }
        }
    }
}
