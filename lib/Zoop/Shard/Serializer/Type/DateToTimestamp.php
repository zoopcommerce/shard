<?php

/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\Shard\Serializer\Type;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Serializes dateTime objects
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DateToTimestamp implements TypeSerializerInterface
{
    public function serialize($value, ClassMetadata $metadata, $field)
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

    public function unserialize($value, ClassMetadata $metadata, $field)
    {
        return new \DateTime("@$value");
    }
}
