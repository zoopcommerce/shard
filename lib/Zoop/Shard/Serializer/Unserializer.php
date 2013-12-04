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
use Zoop\Shard\Exception;
use Zoop\Shard\Core\ModelManagerAwareInterface;
use Zoop\Shard\Core\ModelManagerAwareTrait;

/**
 * Provides methods for unserializing models
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Unserializer implements ServiceLocatorAwareInterface, ModelManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use ModelManagerAwareTrait;

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
        $serializerMetadata = $metadata->getSerializer();

        foreach ($metadata->getFieldNames() as $field) {
            if (isset($serializerMetadata['fields'][$field]['unserializeIgnore']) &&
                $serializerMetadata['fields'][$field]['unserializeIgnore']
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
     * @param  array $data
     * @param  type  $class
     * @param  type  $document
     * @param  type  $mode
     * @return type
     */
    public function fromArray(
        array $data,
        $class,
        $document = null,
        $mode = self::UNSERIALIZE_PATCH
    ) {
        return $this->unserialize($data, $class, $document, $mode);
    }

    /**
     *
     * @param  type $data
     * @param  type $class
     * @param  type $document
     * @param  type $mode
     * @return type
     */
    public function fromJson(
        $data,
        $class,
        $document = null,
        $mode = self::UNSERIALIZE_PATCH
    ) {
        return $this->unserialize(json_decode($data, true), $class, $document, $mode);
    }

    /**
     *
     * @param  array                            $data
     * @param  array                            $className
     * @param  type                             $mode
     * @param  type                             $document
     * @return type
     * @throws Exception\ClassNotFoundException
     */
    protected function unserialize(
        array $data,
        $class,
        $document = null,
        $mode = self::UNSERIALIZE_PATCH
    ) {
        $metadata = $this->modelManager->getClassMetadata($class);

        // Check for discrimnator and discriminator field in data
        if (isset($metadata->discriminatorField) && isset($data[$metadata->discriminatorField['fieldName']])) {
            $metadata = $this->modelManager->getClassMetadata(
                $metadata->discriminatorMap[$data[$metadata->discriminatorField['fieldName']]]
            );
        }

        // Check for reference
        if (isset($data['$ref'])) {
            $pieces = explode('/', $data['$ref']);

            return $this->modelManager->getRepository($metadata->name)->find($pieces[count($pieces) - 1]);
        }

        // Attempt to load prexisting model
        if (! isset($document) && isset($data[$metadata->identifier])) {
            $document = $this->modelManager->getRepository($metadata->name)->find($data[$metadata->identifier]);
        }

        $newInstance = false;
        if (! isset($document)) {
            $document = $metadata->newInstance();
            $newInstance = true;
        }

        foreach ($this->fieldList($metadata, $newInstance) as $field) {
            $this->unserializeField($data, $metadata, $document, $field, $mode);
        }

        return $document;
    }

    protected function unserializeField($data, ClassMetadata $metadata, $document, $field, $mode)
    {
        if ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field)) {
            $value = $this->unserializeSingleModel($data, $metadata, $document, $field, $mode);
        } elseif ($metadata->hasAssociation($field)) {
            $value = $this->unserializeCollection($data, $metadata, $document, $field, $mode);
        } else {
            $value = $this->unserializeSingleValue($data, $metadata, $field);
        }

        if (isset($value)) {
            $metadata->setFieldValue($document, $field, $value);
        } elseif ($mode == self::UNSERIALIZE_UPDATE) {
            $metadata->setFieldValue($document, $field, null);
        }
    }

    protected function unserializeSingleModel($data, ClassMetadata $metadata, $document, $field, $mode)
    {
        if (!isset($data[$field])) {
            return null;
        }

        $mapping = $metadata->fieldMappings[$field];

        if (isset($data[$field]['$ref'])) {
            return $this->getDocumentFromRef($data[$field]['$ref'], $mapping);
        }

        if (is_string($data[$field])) {
            $document = $this->modelManager->getRepository($metadata->name)->find($data[$field]);
        }

        if (isset($mapping['discriminatorMap'])) {
            $discriminatorField = isset($mapping['discriminatorField']) ?
                    $mapping['discriminatorField'] :
                    '_doctrine_class_name';

            $targetClass = $mapping['discriminatorMap'][$data[$field][$discriminatorField]];
        } else {
            $targetClass = $metadata->getAssociationTargetClass($field);
        }

        return $this->unserialize(
            $data[$field],
            $targetClass,
            $metadata->getFieldValue($document, $field),
            $mode
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function unserializeCollection($data, ClassMetadata $metadata, $document, $field, $mode)
    {
        if ($mode == self::UNSERIALIZE_UPDATE || !($collection = $metadata->getFieldValue($document, $field))) {
            $collection = new ArrayCollection;
        }

        if (isset($data[$field])) {
            $targetClass = $metadata->getAssociationTargetClass($field);
            $mapping = $metadata->fieldMappings[$field];

            foreach ($data[$field] as $index => $dataItem) {
                if (isset($dataItem['$ref'])) {
                    if (isset($collection[$index])) {
                        $collection[$index] = $this->getDocumentFromRef($dataItem['$ref'], $mapping);
                    } else {
                        $collection[] = $this->getDocumentFromRef($dataItem['$ref'], $mapping);
                    }
                } else {
                    if (isset($mapping['discriminatorMap'])) {
                        $discriminatorField = isset($mapping['discriminatorField']) ?
                            $mapping['discriminatorField'] :
                            '_doctrine_class_name';
                        $targetClass = $mapping['discriminatorMap'][$dataItem[$discriminatorField]];
                    }

                    if (isset($collection[$index])) {
                        $collection[$index] = $this->unserialize($dataItem, $targetClass, $collection[$index], $mode);
                    } else {
                        $collection[] = $this->unserialize($dataItem, $targetClass);
                    }
                }
            }
        } elseif ($mode == self::UNSERIALIZE_UPDATE) {
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

    protected function getDocumentFromRef($ref, array $mapping)
    {
        list($collectionName, $id) = explode('/', $ref);
        if (isset($mapping['discriminatorMap'])) {
            foreach ($mapping['discriminatorMap'] as $class) {
                if ($this->modelManager->getClassMetadata($class)->collection == $collectionName) {
                    $targetClass = $class;
                    break;
                }
            }
        } else {
            $targetClass = $mapping['targetDocument'];
        }
        return $this->modelManager->getRepository($targetClass)->find($id);
    }

    protected function getTypeSerializer($type)
    {
        return $this->serviceLocator->get($this->typeSerializers[$type]);
    }
}
