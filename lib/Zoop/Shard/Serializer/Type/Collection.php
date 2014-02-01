<?php

/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\Shard\Serializer\Type;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Serializes dateTime objects
 *
 * @since   1.0
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class Collection implements TypeSerializerInterface
{
    public function serialize($value)
    {
        if ($value instanceof ArrayCollection) {
            return $value->toArray();
        } elseif (is_array($value)) {
            return $value;
        }
    }

    public function unserialize($value)
    {
        return $value;
    }
}
