<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zoop\Shard\Core\AbstractEventArgs;
use Zoop\Shard\Core\RejectInterface;
use Zoop\Shard\Core\RejectTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class FreezerEventArgs extends AbstractEventArgs implements RejectInterface
{
    use RejectTrait;

    public function __construct($document, ClassMetadata $metadata, EventManager $eventManager)
    {
        $this->document = $document;
        $this->metadata = $metadata;
        $this->eventManager = $eventManager;
    }
}
