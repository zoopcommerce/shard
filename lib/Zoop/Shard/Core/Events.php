<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
final class Events
{

    /**
     * Triggered by object creation
     */
    const CREATE = 'create';

    /**
     * Triggered by object update
     */
    const UPDATE = 'update';

    /**
     * Triggered by object delete
     */
    const DELETE = 'delete';

    /**
     *
     */
    const VALIDATE = 'validate';

    /**
     *
     */
    const CRYPT = 'crypt';


    const LOAD_METADATA = 'loadMetadata';

    /**
     * Triggered by classmetadata __sleep
     */
    const METADATA_SLEEP = 'metadataSleep';
}