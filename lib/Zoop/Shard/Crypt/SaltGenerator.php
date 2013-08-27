<?php
/**
 * @link       http://zoopcommerce.github.io/common
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt;

use Zend\Math\Rand;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 */
class SaltGenerator
{
    /**
     * Generates a random string to be used as a salt
     *
     * @return string
     */
    public static function generateSalt($minSaltLength = 30)
    {
        return substr(base64_encode(Rand::getBytes($minSaltLength)), 0, $minSaltLength);
    }
}
