<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zoop\Shard\ODMCore\CoreEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class FreezerEventArgs extends CoreEventArgs
{
    protected $recompute = false;

    public function getRecompute() {
        return $this->recompute;
    }

    public function setRecompute($recompute) {
        $this->recompute = $recompute;
    }

    public function __construct($document, ClassMetadata $metadata, EventManager $eventManager) {
        $this->document = $document;
        $this->metadata = $metadata;
        $this->eventManager = $eventManager;
    }
}
