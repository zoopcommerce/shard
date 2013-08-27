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
final class GreaterThanEqual extends Annotation
{
    const EVENT = 'annotationGreaterThanEqualValidator';

    public $value = true;

    public $compareValue;

    public $class = 'Zoop\Mystique\GreaterThanEqual';
}
