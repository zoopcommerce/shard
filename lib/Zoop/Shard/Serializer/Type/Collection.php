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
    public function serialize($value, ClassMetadata $metadata, $field)
    {
        if (empty($value)) {
            return array();
        }
        
        //change value to array
        if (!is_array($value)) {
            if ($value instanceof ArrayCollection) {
                $value = $value->toArray();
            } elseif ($value instanceof ArrayObject) {
                $value = $value->getArrayCopy();
            }
        }

        return $value;
    }

    public function unserialize($value, ClassMetadata $metadata, $field)
    {
        $array = array();
        $serializerMetadata = $metadata->getSerializer();

        //reset value to array
        if (!is_array($value)) {
            if ($value instanceof ArrayCollection) {
                $value = $value->toArray();
            } elseif ($value instanceof ArrayObject) {
                $value = $value->getArrayCopy();
            }
        }
        
        if (isset($serializerMetadata['fields'][$field]['collectionType'])) {
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
        } else {
            $array = (array) $value;
        }
        
        return $array;
    }
}
