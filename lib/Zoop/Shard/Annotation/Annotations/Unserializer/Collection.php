<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation\Annotations\Unserializer;

use Doctrine\Common\Annotations\Annotation;

/**
 * Mark a field to be unserialized into either an array or ArrayCollection
 *
 * @since   1.0
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 *
 * @Annotation
 */
final class Collection extends Annotation
{
    const EVENT = 'annotationUnserializerCollection';

    public $value = true;
    public $type;
}
