<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventArgs as BaseEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
abstract class CoreEventArgs extends BaseEventArgs
{
    protected $document;

    protected $metadata;

    protected $eventManager;

    protected $reject = false;

    public function getDocument() {
        return $this->document;
    }

    public function getMetadata() {
        return $this->metadata;
    }

    public function getEventManager() {
        return $this->eventManager;
    }

    public function getReject() {
        return $this->reject;
    }

    public function setReject($reject) {
        $this->reject = (boolean) $reject;
    }
}
