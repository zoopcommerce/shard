<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zoop\Common\State\Transition;
use Zoop\Shard\Core\AbstractEventArgs;
use Zoop\Shard\Core\RejectInterface;
use Zoop\Shard\Core\RejectTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class TransitionEventArgs extends AbstractEventArgs implements RejectInterface
{
    use RejectTrait;

    protected $transition;

    public function getTransition() {
        return $this->transition;
    }

    public function __construct($document, ClassMetadata $metadata, Transition $transition, EventManager $eventManager) {
        $this->document = $document;
        $this->metadata = $metadata;
        $this->transition = $transition;
        $this->eventManager = $eventManager;
    }
}
