<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Serializer;

use Doctrine\Common\Annotations\Annotation;

/**
 * Shorthand for @ReferenceSerializer("Zoop\Shard\Serializer\Reference\Eager")
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 */
final class Eager extends Annotation
{
    const event = 'annotationSerializerEager';

    public $value = true;
}