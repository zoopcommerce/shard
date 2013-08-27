<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Serializer;

use Doctrine\Common\Annotations\Annotation;

/**
 * Shorthand for @ReferenceSerializer("Zoop\Shard\Serializer\Reference\SimpleLazy")
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 */
final class SimpleLazy extends Annotation
{
    const EVENT = 'annotationSerializerSimpleLazy';

    public $value = true;
}
