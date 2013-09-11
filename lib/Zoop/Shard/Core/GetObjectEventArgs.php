<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Doctrine\Common\EventArgs as BaseEventArgs;
use Doctrine\Common\EventManager as BaseEventManager;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class GetObjectEventArgs extends BaseEventArgs
{
    protected $id;

    protected $class;

    protected $eventManager;

    protected $object;

    public function getId() {
        return $this->id;
    }

    public function getClass() {
        return $this->class;
    }

    public function getEventManager() {
        return $this->eventManager;
    }

    public function getObject() {
        return $this->object;
    }

    public function setObject($object) {
        $this->object = $object;
    }

    public function __construct($id, $class, BaseEventManager $eventManager){
        $this->id = $id;
        $this->class = $class;
        $this->eventManager = $eventManager;
    }
}
