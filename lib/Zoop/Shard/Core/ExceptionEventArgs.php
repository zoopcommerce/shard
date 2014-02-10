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
    protected $innerEvent;

    public function getInnerEvent()
    {
        return $this->innerEvent;
    }

    public function __construct(BaseEventArgs $innerEvent)
    {
        $this->innerEvent = $innerEvent;
    }
}
