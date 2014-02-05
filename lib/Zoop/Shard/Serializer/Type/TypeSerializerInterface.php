<?php

/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\Shard\Serializer\Type;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Serializes dataTime objects
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
interface TypeSerializerInterface
{
    public function serialize($value, ClassMetadata $metadata, $field);

    public function unserialize($value, ClassMetadata $metadata, $field);
}
