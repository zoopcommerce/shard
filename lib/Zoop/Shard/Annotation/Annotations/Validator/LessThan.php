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
final class LessThan extends Annotation
{
    const event = 'annotationLessThanValidator';

    public $value = true;

    public $compareValue;

    public $class = 'Zoop\Mystique\LessThan';
}