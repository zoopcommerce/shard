<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Core\Events;
use Zoop\Shard\Core\ExceptionEventArgs;
use Zoop\Shard\Core\EventManagerTrait;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ExceptionEventsAggregator implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use EventManagerTrait;

    protected $exceptionEvents;

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

    public function __call($name, $args)
    {
        if (!($args[0] instanceof EventArgs)) {
            return;
        }

        $this->getEventManager()->dispatchEvent(Events::EXCEPTION, new ExceptionEventArgs($name, $args[0]));
    }
}
