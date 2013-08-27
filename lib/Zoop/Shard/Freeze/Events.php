<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

/**
 * Provides constants for event names used by the freeze extension
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
final class Events
{

    /**
     * Fires before freeze happens
     */
    const PRE_FREEZE = 'preFreeze';

    /**
     * Fires after freeze happens
     */
    const POST_FREEZE = 'postFreeze';

    /**
     * Fires before a frozen document is thawed
     */
    const PRE_THAW = 'preThaw';

    /**
     * Fires after a frozen document is thawed
     */
    const POST_THAW = 'postThaw';

    /**
     * Triggered when active user attempts to freeze a document they don't have permission
     * for
     */
    const FREEZE_DENIED = 'freezeDenied';

    /**
     * Triggers when active user attempts to thaw a document they don't have permission
     * for
     */
    const THAW_DENIED = 'thawDenied';

    const FROZEN_UPDATE_DENIED = 'frozenUpdateDenied';

    const FROZEN_DELETE_DENIED = 'frozenDeleteDenied';
}
