<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Serializer;

use Doctrine\Common\Annotations\Annotation;

/**
 * May be used in two contexts:
 *
 *     Serializer class annotation
 *         If true, the serializer will add the class name field to serialization
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 */
final class ClassName extends Annotation
{

    const EVENT = 'annotationSerializerClassName';

    public $value = true;
}
