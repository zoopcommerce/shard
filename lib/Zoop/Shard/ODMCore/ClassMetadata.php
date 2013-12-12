<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ODM\MongoDB\Proxy\Proxy;
use Zoop\Shard\Core\ClassMetadataTrait;

/**
 * Extends ClassMetadata to support Shard metadata
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ClassMetadata extends DoctrineClassMetadata
{
    use ClassMetadataTrait;

    /**
     * Sets the specified field to the specified value on the given document.
     *
     * @param object $document
     * @param string $field
     * @param mixed $value
     */
    public function setFieldValue($document, $field, $value)
    {
        if ($document instanceof Proxy && ! $document->__isInitialized()) {
            //property changes to an uninitialized proxy will not be tracked or persisted,
            //so the proxy needs to be loaded first.
            $document->__load();
        }
        $this->reflFields[$field]->setValue($document, $value);
    }

    /**
      * Gets the specified field's value off the given document.
      *
      * @param object $document
      * @param string $field
      */
    public function getFieldValue($document, $field)
    {
        if ($document instanceof Proxy && ! $document->__isInitialized()) {
            if ($field === $this->identifier) {
                return $document->__identifier__;
            } else {
                $document->__load();
            }
        }
        return $this->reflFields[$field]->getValue($document);
    }
}
