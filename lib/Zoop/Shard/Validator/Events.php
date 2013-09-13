<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

/**
 * Provides constants for event names used by the validator extension
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
final class Events
{
    /**
     * Fires if an invalid document is created or updated
     */
    const INVALID_OBJECT = 'invalidObject';
}
