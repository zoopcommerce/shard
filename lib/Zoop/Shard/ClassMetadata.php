<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as DoctrineClassMetadata;

/**
 * Extends ClassMetadata to support Shard metadata
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ClassMetadata extends DoctrineClassMetadata
{

    /**
     * Determines which fields get serialized.
     *
     * @return array The names of all the fields that should be serialized.
     */
    public function __sleep()
    {
        $serialized = parent::__sleep();

        if (isset($this->accessControl)){
            $serialized[] = 'accessControl';
        }
        if (isset($this->password)){
            $serialized[] = 'password';
        }
        if (isset($this->crypt)){
            $serialized[] = 'crypt';
        }
        if (isset($this->generator)){
            $serialized[] = 'generator';
        }
        if (isset($this->freeze)){
            $serialized[] = 'freeze';
        }
        if (isset($this->owner)){
            $serialized[] = 'owner';
        }
        if (isset($this->permissions)){
            $serialized[] = 'permissions';
        }
        if (isset($this->roles)){
            $serialized[] = 'roles';
        }
        if (isset($this->serializer)){
            $serialized[] = 'serializer';
        }
        if (isset($this->softDelete)){
            $serialized[] = 'softDelete';
        }
        if (isset($this->stamp)){
            $serialized[] = 'stamp';
        }
        if (isset($this->state)){
            $serialized[] = 'state';
        }
        if (isset($this->validator)){
            $serialized[] = 'validator';
        }
        if (isset($this->zones)){
            $serialized[] = 'zones';
        }

        return $serialized;
    }
}
