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
abstract class AbstractChangeEventArgs extends AbstractEventArgs implements RejectInterface
{
    use RejectTrait;

    protected $changeSet;

    protected $recompute = [];

    public function getChangeSet()
    {
        return $this->changeSet;
    }

    public function getRecompute()
    {
        return $this->recompute;
    }

    public function setRecompute(array $recompute)
    {
        $this->recompute = $recompute;
    }

    public function addRecompute($field)
    {
        $this->recompute[] = $field;
    }

    public function __construct($document, ClassMetadata $metadata, ChangeSet $changeSet, BaseEventManager $eventManager)
    {
        $this->document = $document;
        $this->metadata = $metadata;
        $this->changeSet = $changeSet;
        $this->eventManager = $eventManager;
    }
}
