<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Serializer;

use Doctrine\Common\Annotations\Annotation;

/**
 * Serializer class annotation. If true, the serializer will add the
 * discriminator field to serialization
 *
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 */
final class Discriminator extends Annotation
{

    const EVENT = 'annotationSerializerDiscriminator';

    public $value = true;
}
