<?php
/**
 * @link       http://zoopcommerce.github.io/common
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

/**
 * Inteface to define a permission on an ControlledObject
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
interface PermissionInterface
{
    const WILD = '*';

    public function areAllowed(array $roles, array $action);
}
