<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
final class Events
{

    /**
     * Triggered by onFlush document creation
     */
    const CREATE = 'create';

    /**
     * Triggered by onFlush document update
     */
    const UPDATE = 'update';

    /**
     * Triggered by onFlush document delete
     */
    const DELETE = 'delete';

    /**
     * Triggered by onFlush
     */
    const VALIDATE = 'validate';

    /**
     * Triggered by onFlush
     */
    const CRYPT = 'crypt';

    /**
     * Triggered by classmetadata __sleep
     */
    const METADATA_SLEEP = 'metadataSleep';
}
