<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze\AccessControl;

/**
 * Defines commonly used action constants
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Actions
{
    /**
     * Mark a resouce as frozen
     */
    const FREEZE = 'freeze';

    /**
     * Unmark a resouce as frozen
     */
    const THAW = 'thaw';
}
