<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Serializer;

use Doctrine\Common\Annotations\Annotation;
use Zoop\Shard\Serializer\Serializer as Constants;

/**
 * Mark a field to be skipped during serialization. Must be used in the context
 * of the Serializer annotation
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 */
final class Ignore extends Annotation
{
    const EVENT = 'annotationSerializerIgnore';

    public $value = Constants::IGNORE_ALWAYS;
}
