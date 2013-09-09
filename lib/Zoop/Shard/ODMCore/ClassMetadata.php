<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as DoctrineClassMetadata;

/**
 * Extends ClassMetadata to support Shard metadata
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ClassMetadata extends DoctrineClassMetadata
{

    protected $eventManager;

    public function getEventManager() {
        return $this->eventManager;
    }

    public function setEventManager($eventManager) {
        $this->eventManager = $eventManager;
    }

    /**
     * Determines which fields get serialized.
     *
     * @return array The names of all the fields that should be serialized.
     */
    public function __sleep()
    {
        $serialized = parent::__sleep();

        $eventArgs = new MetadataSleepEventArgs($this, parent::__sleep(), $this->eventManager);
        $this->eventManager->dispatch(Events::METADATA_SLEEP, $eventArgs);

        return $eventArgs->getSerialized();
    }
}
