<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
final class Events
{

    /**
     * Triggered when user attempts to create a document they don't have permission
     * for
     */
    const CREATE_DENIED = 'createDenied';

    /**
     * Triggers when user attempts to update a document they don't have permission
     * for
     */
    const UPDATE_DENIED = 'updateDenied';

    /**
     * Triggers wehn user attempts to delete a document they don't have permission
     * for
     */
    const DELETE_DENIED = 'deleteDenied';
}
