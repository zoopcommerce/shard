<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * Defines a validator to be used.
 *
 * @Annotation
 */
final class Validator extends Annotation
{
    const EVENT = 'annotationValidator';

    /**
     * The FQCN of the validator to use
     * Class must implement Zoop\Mystique\ValidatorInteface
     *
     * @var string
     */
    public $class;

    /**
     * An array of options to be passed to the class constructor
     *
     * @var array
     */
    public $options = [];

    public $value = true;
}
