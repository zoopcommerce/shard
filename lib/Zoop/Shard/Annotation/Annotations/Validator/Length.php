<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Validator;

use Doctrine\Common\Annotations\Annotation;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation

 */
final class Length extends Annotation
{
    const event = 'annotationLengthValidator';

    public $value = true;

    public $min;

    public $max;

    public $class = 'Zoop\Mystique\Length';
}