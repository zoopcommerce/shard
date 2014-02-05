<?php
/**
 * @link       http://superdweebie.com
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Test\Serializer\TestAsset;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zoop\Shard\Serializer\Type\TypeSerializerInterface;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class StringSerializer implements TypeSerializerInterface
{
    public function serialize(ClassMetadata $metadata, $value, $field)
    {
        return ucfirst($value);
    }

    public function unserialize(ClassMetadata $metadata, $value, $field)
    {
        return lcfirst($value);
    }
}
