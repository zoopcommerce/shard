<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Validator;

use Doctrine\Common\Annotations\Annotation;

/**
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * Defines a validator chain to be used.
 *
 * @Annotation
 */
final class Chain extends Annotation {

    const event = 'annotationChainValidator';

    public $value = [];

    public $class = 'Zoop\Mystique\Chain';
}