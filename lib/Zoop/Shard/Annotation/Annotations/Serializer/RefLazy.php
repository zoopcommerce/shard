<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Serializer;

use Doctrine\Common\Annotations\Annotation;

/**
 * Shorthand for @ReferenceSerializer("Zoop\Shard\Serializer\Reference\RefLazy")
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 */
final class RefLazy extends Annotation
{
    const event = 'annotationSerializerRefLazy';

    public $value = true;
}