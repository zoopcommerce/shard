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
class DeleteEventArgs extends CoreEventArgs
{
    public function __construct($document, ClassMetadata $metadata, EventManager $eventManager) {
        $this->document = $document;
        $this->metadata = $metadata;
        $this->eventManager = $eventManager;
    }
}
