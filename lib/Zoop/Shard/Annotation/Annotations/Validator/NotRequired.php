<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Validator;

use Doctrine\Common\Annotations\Annotation;

/**
 * Annotation to mark a field as required
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation

 */
final class NotRequired extends Annotation
{
    const EVENT = 'annotationNotRequiredValidator';

    public $value = true;

    public $class = 'Zoop\Mystique\NotRequired';
}
