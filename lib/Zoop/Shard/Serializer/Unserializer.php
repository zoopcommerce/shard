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
        if (isset($metadata->discriminatorField) && isset($data[$metadata->discriminatorField])) {
            $metadata = $this->modelManager->getClassMetadata(
                $metadata->discriminatorMap[$data[$metadata->discriminatorField]]
            );
        }

        // Check for reference
        if (isset($data['$ref'])) {
            return $this->modelManager->getRepository($metadata->name)->find($data['$ref']);
        }

        // Attempt to load prexisting model
        if (!isset($document) && isset($data[$metadata->identifier])) {
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

        if (isset($data[$field]['$ref'])) {
            return $this->modelManager
                ->getRepository($this->getTargetClass($metadata, $data[$field], $field))
                ->find($data[$field]['$ref']);
        }

        if (is_string($data[$field])) {
            $document = $this->modelManager->getRepository($metadata->name)->find($data[$field]);
        }

        $targetClass = $this->getTargetClass($metadata, $data[$field], $field);

        return $this->unserialize(
            $data[$field],
            $targetClass,
            $metadata->getFieldValue($document, $field),
            $mode
        );
    }

    protected function unserializeCollection($data, ClassMetadata $metadata, $document, $field, $mode)
    {
        $collection = new ArrayCollection;

        if (isset($data[$field])) {
            foreach ($data[$field] as $dataItem) {
                $targetClass = $this->getTargetClass($metadata, $dataItem, $field);
                if (isset($dataItem['$ref'])) {
                    $collection[] = $this->modelManager->getRepository($targetClass)->find($dataItem['$ref']);
                } else {
                    $collection[] = $this->unserialize($dataItem, $targetClass, null, $mode);
                }
            }
        } elseif ($mode == self::UNSERIALIZE_PATCH &&
            $existingCollection = $metadata->getFieldValue($document, $field)
        ) {
            return $existingCollection;
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
            return $this->serviceLocator->get($this->typeSerializers[$type])
                ->unserialize($data[$field], $metadata, $field);
        }
        if ($type == 'float' && is_integer($data[$field])) {
            return (float) $data[$field];
        }

        return $data[$field];
    }

    protected function getTargetClass(ClassMetadata $metadata, $data, $field)
    {
        $mapping = $metadata->fieldMappings[$field];
        if (isset($mapping['discriminatorMap'])) {
            $discriminatorField = isset($mapping['discriminatorField']) ?
            $mapping['discriminatorField'] :
            '_doctrine_class_name';
            $targetClass = $mapping['discriminatorMap'][$data[$discriminatorField]];
        } elseif (isset($mapping['targetDocument']) && class_exists($mapping['targetDocument'])) {
            $targetClass = $mapping['targetDocument'];
        } else {
            $targetClass = $metadata->getAssociationTargetClass($field);
        }

        return $targetClass;
    }
}
