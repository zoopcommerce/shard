<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Crypt;

use Doctrine\Common\Annotations\Annotation;

/**
 * Cryptographically hash the field value before persisting
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Hash extends Annotation
{
    const event = 'annotationCryptHash';

    /**
     *
     * @var string
     */
    public $service = 'crypt.hash.basichashservice';

    public $salt;
}