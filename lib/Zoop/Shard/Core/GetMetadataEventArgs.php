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
class GetMetadataEventArgs extends BaseEventArgs
{
    protected $class;

    protected $eventManager;

    protected $metadata;

    public function getClass() {
        return $this->class;
    }

    public function getEventManager() {
        return $this->eventManager;
    }

    public function getMetadata() {
        return $this->metadata;
    }

    public function setMetadata($metadata) {
        $this->metadata = $metadata;
    }

    public function __construct($class, BaseEventManager $eventManager){
        $this->class = $class;
        $this->eventManager = $eventManager;
    }
}
