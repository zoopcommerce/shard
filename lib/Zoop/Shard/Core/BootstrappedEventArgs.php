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
class BootstrappedEventArgs extends BaseEventArgs
{
    protected $modelManager;

    protected $eventManager;

    public function getModelManager()
    {
        return $this->modelManager;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function __construct(ModelManager $modelManager, BaseEventManager $eventManager)
    {
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;
    }
}
