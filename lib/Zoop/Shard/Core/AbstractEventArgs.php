<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Doctrine\Common\EventArgs as BaseEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
abstract class AbstractEventArgs extends BaseEventArgs
{
    protected $document;

    protected $metadata;

    protected $eventManager;

    public function getDocument() {
        return $this->document;
    }

    public function getMetadata() {
        return $this->metadata;
    }

    public function getEventManager() {
        return $this->eventManager;
    }
}
