<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Doctrine\Common\EventManager as BaseEventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DeleteEventArgs extends AbstractEventArgs implements RejectInterface
{
    use RejectTrait;

    public function __construct($document, ClassMetadata $metadata, BaseEventManager $eventManager)
    {
        $this->document = $document;
        $this->metadata = $metadata;
        $this->eventManager = $eventManager;
    }
}
