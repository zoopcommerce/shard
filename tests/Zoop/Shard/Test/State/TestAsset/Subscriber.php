<?php

namespace Zoop\Shard\Test\State\TestAsset;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\State\TransitionEventArgs;
use Zoop\Shard\State\Events as StateEvents;

class Subscriber implements EventSubscriber
{
    protected $calls = array();

    protected $rollbackTransition = false;

    public function getSubscribedEvents()
    {
        return array(
            StateEvents::PRE_TRANSITION,
            StateEvents::POST_TRANSITION
        );
    }

    public function reset()
    {
        $this->calls = array();
        $this->rollbackTransition = false;
    }

    public function preTransition(TransitionEventArgs $eventArgs)
    {
        $this->calls['preTransition'] = $eventArgs;
        if ($this->rollbackTransition) {
            $eventArgs->setReject(true);
        }
    }

    public function getRollbackTransition()
    {
        return $this->rollbackTransition;
    }

    public function setRollbackTransition($rollbackTransition)
    {
        $this->rollbackTransition = $rollbackTransition;
    }

    public function getCalls()
    {
        return $this->calls;
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments[0];
    }
}
