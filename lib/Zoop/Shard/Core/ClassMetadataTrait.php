<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Zoop\Shard\Exception\InvalidArgumentException;

/**
 * Extends ClassMetadata to support Shard metadata
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 */
trait ClassMetadataTrait
{
    protected $extensionProperties = [];

    public function addProperty($name, $sleep = false)
    {
        $this->extensionProperties[$name] = [
            'value' => null,
            'sleep' => $sleep
        ];
    }

    public function hasProperty($property)
    {
        return isset($this->extensionProperties[$property]);
    }

    public function __call($name, $args)
    {
        $type = substr($name, 0, 3);
        $property = lcfirst(substr($name, 3));
        if ($type == 'get' && isset($this->extensionProperties[$property])) {
            return $this->extensionProperties[$property]['value'];
        } elseif ($type == 'set') {
            if (!isset($this->extensionProperties[$property])) {
                throw new InvalidArgumentException(sprintf('The property name %s is not valid', $property));
            }
            $this->extensionProperties[$property]['value'] = $args[0];
        }
    }

    /**
     * Determines which fields get serialized.
     *
     * @return array The names of all the fields that should be serialized.
     */
    public function __sleep()
    {
        $fields = parent::__sleep();

        foreach ($this->extensionProperties as $field => $settings) {
            if ($settings['sleep']) {
                $fields[] = $field;
            }
        }

        return $fields;
    }
}
