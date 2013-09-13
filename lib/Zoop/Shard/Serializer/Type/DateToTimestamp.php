<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer\Type;

/**
 * Serializes dateTime objects
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DateToTimestamp implements TypeSerializerInterface
{
    public function serialize($value)
    {
        switch (true) {
            case $value instanceof \MongoDate:
                return $value->sec;
                break;
            case $value instanceof \DateTime:
                return (integer) $value->format('U');
                break;
        }
    }

    public function unserialize($value)
    {
        return new \DateTime("@$value");
    }
}
