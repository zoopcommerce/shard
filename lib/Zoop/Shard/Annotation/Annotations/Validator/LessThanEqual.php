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
final class LessThanEqual extends Annotation
{
    const event = 'annotationLessThanEqualValidator';

    public $value = true;

    public $compareValue;

    public $class = 'Zoop\Mystique\LessThanEqual';
}