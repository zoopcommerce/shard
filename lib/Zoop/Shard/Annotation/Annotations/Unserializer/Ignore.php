<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Unserializer;

use Doctrine\Common\Annotations\Annotation;

/**
 * Mark a field to be skipped during unserialization.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 */
final class Ignore extends Annotation
{
    const EVENT = 'annotationUnserializerIgnore';

    public $value = true;
}
