<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Proxy;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Core\ObjectManagerAwareInterface;
use Zoop\Shard\Core\ObjectManagerAwareTrait;

/**
 * Provides methods for serializing documents
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Serializer implements ServiceLocatorAwareInterface, ObjectManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use ObjectManagerAwareTrait;

    /** @var array */
    protected $typeSerializers = [];

    /** @var int */
    protected $maxNestingDepth;

    /** @var int */
    protected $nestingDepth;

    public function setTypeSerializers(array $typeSerializers)
    {
        $this->typeSerializers = $typeSerializers;
    }

    /**
     * @param int $maxNestingDepth
     */
    public function setMaxNestingDepth($maxNestingDepth)
    {
        $this->maxNestingDepth = (int) $maxNestingDepth;
    }

    /**
     *
     * @param object $document
     * @param DocumentManager $documentManager
     * @return array
     */
    public function toArray($document)
    {
        return $this->serialize($document);
    }

    /**
     *
     * @param object $document
     * @param DocumentManager $documentManager
     * @return string
     */
    public function toJson($document)
    {
        return json_encode($this->serialize($document));
    }

    public function fieldList(ClassMetadata $metadata)
    {
        $return = [];

        foreach ($metadata->getFieldNames() as $field) {
            if ($this->isSerializableField($field, $metadata)) {
                $return[] = $field;
            }
        }

        return $return;
    }

    public function isSerializableField($field, ClassMetadata $metadata)
    {
        if (! isset($metadata->fieldMappings[$field])) {
            return false;
        }
        if (isset($metadata->serializer['fields'][$field]['serializeIgnore']) &&
            $metadata->serializer['fields'][$field]['serializeIgnore']
        ) {
            return false;
        }
        return true;
    }

    /**
     * Will take an associative array representing a document, and apply the
     * serialization metadata rules to that array.
     *
     * @param array $array
     * @param string $className
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     * @return array
     */
    public function applySerializeMetadataToArray(array $array, $className)
    {
        $return = [];
        $metadata = $this->objectManager->getClassMetadata($className);

        if ($metadata->hasDiscriminator()) {
            $return[$metadata->discriminatorField['name']] = $metadata->discriminatorValue;
        }

        //juggle id
        if (isset($array['_id'])) {
            $array[$metadata->getIdentifier()] = $array['_id'];
        }

        foreach ($this->fieldList($metadata) as $field) {
            if (! isset($array[$field])) {
                continue;
            }

            $serializedValue = $this->serializeField($metadata, $array[$field], $field);
            if (isset($serializedValue)) {
                $return[$field] = $serializedValue;
            }
        }

        return $return;
    }

    /**
     *
     * @param object | array $document
     * @param DocumentManager $documentManager
     * @return array
     * @throws \BadMethodCallException
     */
    protected function serialize($document)
    {
        $return = [];

        $metadata = $this->objectManager->getClassMetadata(get_class($document));

        if ($metadata->hasDiscriminator()) {
            $return[$metadata->discriminatorField['name']] = $metadata->discriminatorValue;
        }

        if ($document instanceof Proxy) {
            $document->__load();
        }

        foreach ($this->fieldList($metadata) as $field) {

            $rawValue = $metadata->getFieldValue($document, $field);

            if (! isset($rawValue)) {
                continue;
            }

            $serializedValue = $this->serializeField($metadata, $rawValue, $field);
            if (isset($serializedValue)) {
                $return[$field] = $serializedValue;
            }
        }

        return $return;
    }

    protected function serializeField(ClassMetadata $metadata, $value, $field)
    {
        if ($metadata->hasAssociation($field) &&
            $metadata->isSingleValuedAssociation($field)
        ) {
            return $this->serializeSingleObject($metadata, $value, $field);
        } else if ($metadata->hasAssociation($field)) {
            return $this->serializeCollection($metadata, $value, $field);
        } else {
            return $this->serializeSingleValue($metadata, $value, $field);
        }
    }

    protected function serializeSingleObject(ClassMetadata $metadata, $value, $field)
    {
        $mapping = $metadata->fieldMappings[$field];

        if (isset($mapping['embedded'])) {
            if (is_array($value)) {
                return $this->applySerializeMetadataToArray($value, $mapping['targetDocument']);
            } else {
                return $this->serialize($value);
            }
        }

        //serialize reference
        if ($this->nestingDepth < $this->maxNestingDepth) {
            $this->nestingDepth++;
            $targetMetadata = $this->objectManager->getClassMetadata($mapping['targetDocument']);

            if ($value instanceof Proxy) {
                $id = $value->{'get' . ucfirst($targetMetadata->getIdentifier())}();
            } else if (is_array($value)) {
                $id = $value['$id'];
            } else if (is_string($value)) {
                $id = $value;
            } else {
                $id = $targetMetadata->getFieldValue($value, $targetMetadata->getIdentifier());
            }
            $serializedDocument = $this->getReferenceSerializer($field, $metadata)->serialize($id, $mapping);
            $this->nestingDepth--;
            if ($serializedDocument) {
                return $serializedDocument;
            }
        }
    }

    protected function serializeCollection(ClassMetadata $metadata, $value, $field)
    {
        if (count($value) == 0) {
            return;
        }

        $mapping = $metadata->fieldMappings[$field];
        $return = [];

        if (isset($mapping['embedded'])) {
            foreach ($value as $embedDocument) {
                if (is_array($value)) {
                    $return[] = $this->applySerializeMetadataToArray($embedDocument, $mapping['targetDocument']);
                } else {
                    $return[] = $this->serialize($embedDocument);
                }
            }
        } else {
            if ($this->nestingDepth < $this->maxNestingDepth) {
                $this->nestingDepth++;
                if (method_exists($value, 'getMongoData')) {
                    $valueCollection = $value->getMongoData();
                } else {
                    $valueCollection = $value;
                }
                foreach ($valueCollection as $index => $referenceDocument) {
                    $serializedDocument = $this->getReferenceSerializer($field, $metadata)->serialize(
                        is_array($referenceDocument) ? $referenceDocument['$id'] : (string) $referenceDocument,
                        $mapping
                    );
                    if ($serializedDocument) {
                        $return[$index] = $serializedDocument;
                    }
                }
                $this->nestingDepth--;
            }
        }

        if (count($return) == 0) {
            return;
        }
        return $return;
    }

    protected function serializeSingleValue(ClassMetadata $metadata, $value, $field)
    {
        $type = $metadata->getTypeOfField($field);

        if (array_key_exists($type, $this->typeSerializers)) {
            return $this->getTypeSerializer($type)->serialize($value);
        }
        return $value;
    }

    protected function getReferenceSerializer($field, $metadata)
    {
        if (isset($metadata->serializer['fields'][$field]['referenceSerializer'])) {
            $name = $metadata->serializer['fields'][$field]['referenceSerializer'];
        } else {
            $name = 'serializer.reference.refLazy';
        }
        return $this->serviceLocator->get($name);
    }

    protected function getTypeSerializer($type)
    {
        return $this->serviceLocator->get($this->typeSerializers[$type]);
    }
}
