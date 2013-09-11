<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory as DoctrineClassMetadataFactory;

/**
 * Extends ClassMetadataFactory to support Shard metadata
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ClassMetadataFactory extends DoctrineClassMetadataFactory
{

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     * @return ClassMetadata
     */
    protected function newClassMetadataInstance($className)
    {
        $instance = new ClassMetadata($className);
        $instance->setEventManager($this->evm);
        
        return $instance;
    }
}
