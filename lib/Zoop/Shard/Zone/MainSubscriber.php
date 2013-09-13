<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Zone;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\MetadataSleepEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            CoreEvents::METADATA_SLEEP,
        ];
    }

    public function metadataSleep(MetadataSleepEventArgs $eventArgs)
    {
        if (isset($eventArgs->getMetadata()->zones)) {
            $eventArgs->addSerialized('zones');
        }
    }
}
