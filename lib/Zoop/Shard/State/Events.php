<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
final class Events
{

    /**
     * Called before state change. Can be used to roll the state change back
     */
    const PRE_TRANSITION = 'preTransition';

    /**
     * Called during state change. Can be used to update the workflow vars
     */
    const ON_TRANSITION = 'onTransition';

    /**
     * Called after state change complete
     */
    const POST_TRANSITION = 'postTransition';

    /**
     * Triggered when active user attempts to change state of a document they don't have permission
     * for
     */
    const TRANSITION_DENIED = 'transitionDenied';

    /**
     * Triggered when there is an attempt to set the state to a value that is not
     * part of the state list defined in the @State annotation
     */
    const BAD_STATE = 'badState';
}
