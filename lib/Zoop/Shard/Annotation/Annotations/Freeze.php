<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * Annotation to mark a field as the frozen flag. Field must be a
 * boolean type.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Freeze extends Annotation
{
    const event = 'annotationFreeze';
}