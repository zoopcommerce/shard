<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\BootstrappedEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class BootstrappedSubscriber implements EventSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [CoreEvents::BOOTSTRAPPED];
    }

    public function bootstrapped(BootstrappedEventArgs $eventArgs)
    {
        $filter = $eventArgs->getModelManager()->getFilterCollection()->enable('odmfilter');
        $filter->setEventManager($eventArgs->getEventManager());
    }
}
