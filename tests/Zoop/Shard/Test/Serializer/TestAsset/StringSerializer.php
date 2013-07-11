<?php
/**
 * @link       http://superdweebie.com
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Test\Serializer\TestAsset;

use Zoop\Shard\Serializer\Type\TypeSerializerInterface;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class StringSerializer implements TypeSerializerInterface {

    public function serialize($value) {
        return ucfirst($value);
    }

    public function unserialize($value) {
        return lcfirst($value);
    }
}
