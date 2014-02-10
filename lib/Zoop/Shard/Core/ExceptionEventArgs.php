<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Doctrine\Common\EventArgs as BaseEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ExceptionEventArgs extends BaseEventArgs
{
    protected $name;

    protected $innerEvent;

    public function getName()
    {
        return $this->name;
    }

    public function getInnerEvent()
    {
        return $this->innerEvent;
    }

    public function __construct($name, BaseEventArgs $innerEvent)
    {
        $this->name = (string) $name;
        $this->innerEvent = $innerEvent;
    }
}
