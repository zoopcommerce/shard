<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete\AccessControl;

/**
 * Defines commonly used action constants
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Actions
{
    /**
     * Mark a resouce as deleted, but do not actually remove it
     */
    const SOFT_DELETE = 'softDelete';

    /**
     * Unmark a resource as deleted
     */
    const RESTORE = 'restore';
}
