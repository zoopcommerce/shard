<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt\Hash;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
interface HashServiceInterface
{
    public function hashValue($plaintext, $salt = null);

    public function hashField($field, $document, $metadata);
}
