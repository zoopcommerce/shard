<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Doctrine\Common\EventArgs as BaseEventArgs;
use Doctrine\Common\EventManager as BaseEventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ReadEventArgs extends BaseEventArgs
{
    protected $metadata;

    protected $eventManager;

    protected $criteria = [];

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function addCriteria($criteria)
    {
        $this->criteria[] = $criteria;
    }

    public function __construct(ClassMetadata $metadata, BaseEventManager $eventManager)
    {
        $this->metadata = $metadata;
        $this->eventManager = $eventManager;
    }
}
