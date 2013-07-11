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
    const preFreeze = 'preFreeze';

    /**
     * Fires after freeze happens
     */
    const postFreeze = 'postFreeze';

    /**
     * Fires before a frozen document is thawed
     */
    const preThaw = 'preThaw';

    /**
     * Fires after a frozen document is thawed
     */
    const postThaw = 'postThaw';

    /**
     * Triggered when active user attempts to freeze a document they don't have permission
     * for
     */
    const freezeDenied = 'freezeDenied';

    /**
     * Triggers when active user attempts to thaw a document they don't have permission
     * for
     */
    const thawDenied = 'thawDenied';

    const frozenUpdateDenied = 'frozenUpdateDenied';

    const frozenDeleteDenied = 'frozenDeleteDenied';
}