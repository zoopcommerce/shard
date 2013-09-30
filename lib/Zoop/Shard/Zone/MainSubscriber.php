<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Zone;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\ReadEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            CoreEvents::READ,
        ];
    }

    public function read(ReadEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();

        if (! ($field = $metadata->getZones())) {
            return;
        }

        $extension = $this->serviceLocator->get('extension.zone');
        $include = $extension->getReadFilterInclude();
        $exclude = $extension->getReadFilterExclude();
        $criteria = [];

        if (count($include) > 0) {
            $criteria[$field] = ['$in' => $include];
        }

        if (count($exclude) > 0) {
            $criteria[$field] = ['$nin' => $exclude];
        }

        if (count($criteria) == 0) {
            return;
        }

        $eventArgs->addCriteria($criteria);
    }
}
