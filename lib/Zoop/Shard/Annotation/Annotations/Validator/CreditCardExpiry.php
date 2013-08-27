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
final class CreditCardExpiry extends Annotation
{
    const EVENT = 'annotationCreditCardExpiryValidator';

    public $value = true;

    public $class = 'Zoop\Mystique\CreditCardExpiry';
}
