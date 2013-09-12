<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Exception;
use Zoop\Shard\Core\ObjectManagerAwareInterface;
use Zoop\Shard\Core\ObjectManagerAwareTrait;

/**
 * Provides methods for unserializing documents
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Unserializer implements ServiceLocatorAwareInterface, ObjectManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use ObjectManagerAwareTrait;

    const UNSERIALIZE_UPDATE = 'unserialize_update';
    const UNSERIALIZE_PATCH = 'unserliaze_patch';

    /** @var array */
    protected $typeSerializers = [];

    public function setTypeSerializers(array $typeSerializers)
    {
        $this->typeSerializers = $typeSerializers;
    }
    public function fieldList(ClassMetadata $metadata, $includeId = true)
    {
        $return = [];

        foreach ($metadata->getFieldNames() as $field) {
            if (isset($metadata->serializer['fields'][$field]['unserializeIgnore']) &&
                $metadata->serializer['fields'][$field]['unserializeIgnore']
            ) {
                continue;
            }
            $return[] = $field;
        }

        if (!$includeId) {
            unset($return[$metadata->getIdentifier()]);
        }
        return $return;
    }

    /**
     * This will create a document from the supplied array.
     *
     * @param array $data
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     * @param string $className
     * @return object
     */
    public function fromArray(
        array $data,
        ClassMetadata $metadata,
        $document = null,
        $mode = self::UNSERIALIZE_PATCH
    ) {
        return $this->unserialize($data, $metadata, $document, $mode);
    }

    /**
     * This will create a document from the supplied json string.
     * WARNING: the constructor of the document will not be called.
     *
     * @param string $data
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     * @param string $className
     * @return object
     */
    public function fromJson(
        $data,
        ClassMetadata $metadata,
        $document = null,
        $mode = self::UNSERIALIZE_PATCH
    ) {
        return $this->unserialize(json_decode($data, true), $metadata, $document, $mode);
    }

    /**
     *
     * @param array $data
     * @param array $className
     * @param type $mode
     * @param type $document
     * @return type
     * @throws Exception\ClassNotFoundException
     */
    protected function unserialize(
        array $data,
        ClassMetadata $metadata,
        $document = null,
        $mode = self::UNSERIALIZE_PATCH
    ) {
        // Check for discrimnator and discriminator field in data
        $metadata = $this->resolveMetadata($data, null, $metadata);

        // Check for reference
        if (isset($data['$ref'])) {
            $pieces = explode('/', $data['$ref']);
            return $this->objectManager->getRepository($metadata->name)->find($pieces[count($pieces) - 1]);
        }

        // Attempt to load prexisting object
        if (! isset($document) && isset($data[$metadata->identifier])) {
            $document = $this->objectManager->getRepository($metadata->name)->find($data[$metadata->identifier]);
        }

        $newInstance = false;
        if (! isset($document)) {
            $document = $metadata->newInstance();
            $newInstance = true;
        }

        foreach ($this->fieldList($metadata, $newInstance) as $field) {
            if ($value = $this->unserializeField($data, $metadata, $document, $field, $mode)) {
                $metadata->setFieldValue($document, $field, $value);
            } elseif ($mode == self::UNSERIALIZE_UPDATE) {
                $metadata->setFieldValue($document, $field, null);
            }
        }

        return $document;
    }

    protected function unserializeField($data, ClassMetadata $metadata, $document, $field, $mode)
    {
        if ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field)) {
            return $this->unserializeSingleObject($data, $metadata, $document, $field, $mode);
        } else if ($metadata->hasAssociation($field)) {
            return $this->unserializeCollection($data, $metadata, $document, $field, $mode);
        } else {
            return $this->unserializeSingleValue($data, $metadata, $field);
        }
    }

    protected function unserializeSingleObject($data, ClassMetadata $metadata, $document, $field, $mode)
    {
        if (!isset($data[$field])) {
            return null;
        }

        $targetClass = $metadata->getAssociationTargetClass($field);

        if (isset($data[$field]['$ref'])) {
            $pieces = explode('/', $data[$field]['$ref']);
            return $this->objectManager->getRepository($targetClass)->find($pieces[count($pieces) - 1]);
        }
        if (is_string($data[$field])) {
            $document = $this->objectManager->getRepository($metadata->name)->find($data[$field]);
        }

        $targetMetadata = $this->resolveMetadata($data[$field], $metadata->fieldMappings[$field]);

        return $this->unserialize(
            $data[$field],
            $targetMetadata,
            $metadata->getFieldValue($document, $field),
            $mode
        );
    }

    protected function unserializeCollection($data, ClassMetadata $metadata, $document, $field, $mode)
    {
        if (! ($collection = $metadata->getFieldValue($document, $field))) {
            $collection = new ArrayCollection;
        }

        if (isset($data[$field])) {
            $targetClass = $metadata->getAssociationTargetClass($field);
            $mapping = $metadata->fieldMappings[$field];

            if (!isset($mapping->discriminatorField)) {
                $targetMetadata = $this->objectManager->getClassMetadata($targetClass);
            }

            foreach ($data[$field] as $index => $dataItem) {
                if (isset($mapping->discriminatorField) && isset($data[$mapping->discriminatorField['fieldName']])) {
                    $targetMetadata = $this->objectManager->getClassMetadata($mapping->discriminatorMap[$data[$mapping->discriminatorField['fieldName']]]);
                }
                $collection[$index] = $this->unserialize($dataItem, $targetMetadata, $collection[$index], $mode);
            }
        } else if ($mode == self::UNSERIALIZE_UPDATE) {
            foreach ($collection->getKeys() as $key) {
                $collection->remove($key);
            }
        }

        return $collection;
    }

    protected function unserializeSingleValue($data, ClassMetadata $metadata, $field)
    {
        if (!isset($data[$field])) {
            return null;
        }

        $type = $metadata->getTypeOfField($field);

        if (isset($this->typeSerializers[$type])) {
            return $this->getTypeSerializer($type)->unserialize($data[$field]);
        }
        if ($type == 'float' && is_integer($data[$field])) {
            return (float) $data[$field];
        }

        return $data[$field];
    }

    protected function resolveMetadata($data, $mapping = null, $metadata = null)
    {
        if (isset($metadata->discriminatorField) && isset($data[$metadata->discriminatorField['fieldName']])) {
            $targetClass = $metadata->discriminatorMap[$data[$metadata->discriminatorField['fieldName']]];
        } else if (isset($mapping['discriminatorField']) && isset($data[$mapping['discriminatorField']['fieldName']])) {
            $targetClass = $mapping['discriminatorMap'][$data[$mapping['discriminatorField']['fieldName']]];
        } else if (isset($mapping['targetDocument'])) {
            $targetClass = $mapping['targetDocument'];
        }

        if (isset($targetClass)) {
            return $this->objectManager->getClassMetadata($targetClass);
        }
        return $metadata;
    }

    protected function getTypeSerializer($type)
    {
        return $this->serviceLocator->get($this->typeSerializers[$type]);
    }
}
