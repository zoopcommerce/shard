<?php

/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\Shard\Serializer\Type;

use \ArrayObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Serializes Collection objects
 *
 * @since   1.0
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class Collection implements TypeSerializerInterface
{

    public function serialize(ClassMetadata $metadata, $value, $field)
    {
        //change value to array
        if (!is_array($value)) {
            if ($value instanceof ArrayCollection || $value instanceof ArrayObject) {
                $value = $value->toArray();
            }
        }
        
        return $value;
    }

    public function unserialize(ClassMetadata $metadata, $value, $field)
    {
        $array = null;
        $serializerMetadata = $metadata->getSerializer();

        //reset value to array
        if (!is_array($value)) {
            if ($value instanceof ArrayCollection || $value instanceof ArrayObject) {
                $value = $value->toArray();
            }
        }

        switch ($serializerMetadata['fields'][$field]['collectionType']) {
            case 'ArrayObject':
                $array = new ArrayObject($value);
                break;
            case 'ArrayCollection':
                $array = new ArrayCollection($value);
                break;
            case 'Array':
            case 'array':
            default:
                $array = $value;
                break;
        }
        return $array;
    }

}
