<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class UpdateEventArgs extends CoreEventArgs
{
    protected $changeSet;

    protected $recompute = false;

    public function getChangeSet() {
        return $this->changeSet;
    }

    public function getRecompute() {
        return $this->recompute;
    }

    public function setRecompute($recompute) {
        $this->recompute = $recompute;
    }

    public function __construct($document, ClassMetadata $metadata, array $changeSet, EventManager $eventManager) {
        $this->document = $document;
        $this->metadata = $metadata;
        $this->changeSet = $changeSet;
        $this->eventManager = $eventManager;
    }
}
