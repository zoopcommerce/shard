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
    const PRE_SOFT_DELETE = 'preSoftDelete';

    /**
     * Fires after soft delete happens
     */
    const POST_SOFT_DELETE = 'postSoftDelete';

    /**
     * Fires before a soft deleted document is restored
     */
    const PRE_RESTORE = 'preRestore';

    /**
     * Fires after a soft deleted document is restored
     */
    const POST_RESTORE = 'postRestore';

    /**
     * Fires if an updated is attempted on a soft deleted object
     */
    const SOFT_DELETED_UPDATE_DENIED = 'softDeletedUpdateDenied';

    /**
     * Triggered when active user attempts to soft delete a document they don't have permission
     * for
     */
    const SOFT_DELETE_DENIED = 'softDeleteDenied';

    /**
     * Triggers when active user attempts to restore a document they don't have permission
     * for
     */
    const RESTORE_DENIED = 'restoreDenied';
}
