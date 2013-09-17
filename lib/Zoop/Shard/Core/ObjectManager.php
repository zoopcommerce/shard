<?php

namespace Zoop\Shard\Core;

/**
 * Contract for a Shard persistence layer ObjectManager class to implement.
 * This is a subset of Doctrine\Common\Persistence\ObjectManager
 *
 */
interface ObjectManager
{
    /**
     * Finds a object by its identifier.
     *
     * @param string $className The class name of the object to find.
     * @param mixed  $id        The identity of the object to find.
     *
     * @return object The found object.
     */
    public function find($className, $id);

    /**
     * Returns the ClassMetadata descriptor for a class.
     *
     * The class name must be the fully-qualified class name without a leading backslash
     * (as it is returned by get_class($obj)).
     *
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getClassMetadata($className);
}
