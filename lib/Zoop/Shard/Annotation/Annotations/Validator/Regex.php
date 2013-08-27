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
final class Regex extends Annotation
{
    const EVENT = 'annotationRegexValidator';

    public $value = true;

    public $regex;

    public $class = 'Zoop\Mystique\Regex';
}
