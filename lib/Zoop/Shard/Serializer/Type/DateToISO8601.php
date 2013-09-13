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
class DateToISO8601 implements TypeSerializerInterface
{
    public function serialize($value)
    {
        switch (true) {
            case $value instanceof \MongoDate:
                $value = new \DateTime("@$value->sec");
                //deliberate fall through
            case $value instanceof \DateTime:
                $value->setTimezone(new \DateTimeZone('UTC'));

                return $value->format('c');
                break;
        }
    }

    public function unserialize($value)
    {
        return new \DateTime($value);
    }
}
