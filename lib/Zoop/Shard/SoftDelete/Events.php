<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

/**
 * Provides constants for event names used by the soft delete extension
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
final class Events
{

    /**
     * Fires before soft delete happens
     */
    const preSoftDelete = 'preSoftDelete';

    /**
     * Fires after soft delete happens
     */
    const postSoftDelete = 'postSoftDelete';

    /**
     * Fires before a soft deleted document is restored
     */
    const preRestore = 'preRestore';

    /**
     * Fires after a soft deleted document is restored
     */
    const postRestore = 'postRestore';

    /**
     * Fires if an updated is attempted on a soft deleted object
     */
    const softDeletedUpdateDenied = 'softDeletedUpdateDenied';

    /**
     * Triggered when active user attempts to soft delete a document they don't have permission
     * for
     */
    const softDeleteDenied = 'softDeleteDenied';

    /**
     * Triggers when active user attempts to restore a document they don't have permission
     * for
     */
    const restoreDenied = 'restoreDenied';
}