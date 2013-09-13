<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait RejectTrait
{
    protected $reject = false;

    public function getReject()
    {
        return $this->reject;
    }

    public function setReject($reject)
    {
        $this->reject = (boolean) $reject;
    }
}
