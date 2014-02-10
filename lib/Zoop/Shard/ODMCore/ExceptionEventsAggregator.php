<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Core\Events;
use Zoop\Shard\Core\ExceptionEventArgs;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ExceptionEventsAggregator implements EventSubscriber
{
    protected $exceptionEvents;

    protected $eventManager;

    public function getSubscribedEvents()
    {
        return $this->exceptionEvents;
    }

    public function getExceptionEvents()
    {
        return $this->exceptionEvents;
    }

    public function setExceptionEvents(array $exceptionEvents)
    {
        $this->exceptionEvents = $exceptionEvents;
    }

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __call($name, $args)
    {
        if (!($args[0] instanceof EventArgs)) {
            return;
        }

        $this->eventManager->dispatchEvent(Events::EXCEPTION, new ExceptionEventArgs($args[0]));
    }
}
