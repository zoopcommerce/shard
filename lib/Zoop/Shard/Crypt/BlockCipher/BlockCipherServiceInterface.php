<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt\BlockCipher;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
interface BlockCipherServiceInterface {

    public function encryptValue($plaintext, $key, $salt = null);

    public function decryptValue($encryptedText, $key);
    
    public function encryptField($field, $document, $metadata);

    public function decryptField($field, $document, $metadata);
}
