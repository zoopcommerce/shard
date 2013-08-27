<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Crypt;

use Doctrine\Common\Annotations\Annotation;

/**
 * Encrypt the field value before persisting, and decrypt on retrieval
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class BlockCipher extends Annotation
{
    const EVENT = 'annotationCryptBlockCipher';

    public $service = 'crypt.blockcipher.zendservice';

    public $key;

    public $salt;
}
