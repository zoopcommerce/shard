<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Proxy\Proxy;
use Zoop\Shard\DocumentManagerAwareInterface;
use Zoop\Shard\DocumentManagerAwareTrait;
use Zoop\Shard\Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Provides methods for serializing documents
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Serializer implements ServiceLocatorAwareInterface, DocumentManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use DocumentManagerAwareTrait;

    /** @var array */
    protected $typeSerializers = [];

    /** @var int */
    protected $maxNestingDepth;

    /** @var int */
    protected $nestingDepth;

    protected $classNameField;

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

    public function setClassNameField($classNameField)
    {
        $this->classNameField = (string) $classNameField;
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

    public function applySerializeMetadataToField($value, $field, $className)
    {
        $classMetadata = $this->documentManager->getClassMetadata($className);

        if (! $this->isSerializableField($field, $classMetadata)) {
            return null;
        }

        $mapping = $classMetadata->fieldMappings[$field];

        switch (true){
            case isset($mapping['embedded']) && $mapping['type'] == 'one':
                return $this->applySerializeMetadataToArray(
                    $value,
                    $mapping['targetDocument']
                );
            case isset($mapping['embedded']) && $mapping['type'] == 'many':
                $return = [];
                foreach ($value as $index => $embedArray) {
                    $return[$index] = $this->applySerializeMetadataToArray(
                        $embedArray,
                        $mapping['targetDocument']
                    );
                }
                return $return;
            case isset($mapping['reference']) && $mapping['type'] == 'one':
                if ($this->nestingDepth < $this->maxNestingDepth) {
                    $this->nestingDepth++;
                    $return = $this->getReferenceSerializer($field, $classMetadata)->serialize(
                        is_array($value) ? $value['$id'] : $value,
                        $mapping
                    );
                    $this->nestingDepth--;
                    return $return;
                }
                return null;
            case isset($mapping['reference']) && $mapping['type'] == 'many':
                if ($this->nestingDepth < $this->maxNestingDepth) {
                    $this->nestingDepth++;
                    $return = [];
                    foreach ($value as $index => $referenceDocument) {
                        $return[$index] = $this->getReferenceSerializer($field, $classMetadata)->serialize(
                            is_array($referenceDocument) ? $referenceDocument['$id'] : $referenceDocument,
                            $mapping
                        );
                    }
                    $this->nestingDepth--;
                    return $return;
                }
                return null;
            case array_key_exists($mapping['type'], $this->typeSerializers):
                return $this->getTypeSerializer($mapping['type'])->serialize($value);
            default:
                return $value;
        }
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
        $classMetadata = $this->documentManager->getClassMetadata($className);
        $fieldList = $this->fieldListForSerialize($classMetadata);
        $return = array_merge($array, $this->serializeClassNameAndDiscriminator($classMetadata));

        foreach ($classMetadata->fieldMappings as $field => $mapping) {

            if (! in_array($field, $fieldList)) {
                if (isset($return[$field])) {
                    unset($return[$field]);
                }
                continue;
            }

            if (isset($mapping['id']) && $mapping['id'] && isset($array['_id'])) {
                $return[$field] = $array['_id'];
                unset($return['_id']);
            }

            if (! isset($return[$field])) {
                continue;
            }

            $return[$field] = $this->applySerializeMetadataToField($return[$field], $field, $className);
        }

        return $return;
    }

    protected function serializeClassNameAndDiscriminator(ClassMetadata $metadata)
    {
        $return = array();

        if (isset($metadata->serializer['className']) &&
            $metadata->serializer['className']
        ) {
            $return[$this->classNameField] = $metadata->name;
        }

        if (isset($metadata->serializer['discriminator']) &&
            $metadata->serializer['discriminator'] &&
            $metadata->hasDiscriminator()
        ) {
            $return[$metadata->discriminatorField['name']] = $metadata->discriminatorValue;
        }

        return $return;
    }

    public function fieldListForSerialize(ClassMetadata $classMetadata)
    {
        $return = [];

        foreach ($classMetadata->fieldMappings as $field => $mapping) {
            if ($this->isSerializableField($field, $classMetadata)) {
                $return[] = $field;
            }
        }

        return $return;
    }

    public function isSerializableField($field, ClassMetadata $classMetadata)
    {
        if (! isset($classMetadata->fieldMappings[$field])) {
            return false;
        }
        if (isset($classMetadata->serializer['fields'][$field]['serializeIgnore']) &&
            $classMetadata->serializer['fields'][$field]['serializeIgnore']
        ) {
            return false;
        }
        return true;
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
        $metadata = $this->documentManager->getClassMetadata(get_class($document));
        $return = $this->serializeClassNameAndDiscriminator($metadata);

        if ($document instanceof Proxy) {
            $document->__load();
        }

        foreach ($this->fieldListForSerialize($metadata) as $field) {

            $mapping = $metadata->fieldMappings[$field];
            $rawValue = $metadata->reflFields[$field]->getValue($document);

            switch (true){
                case $rawValue && isset($mapping['embedded']) && $mapping['type'] == 'one':
                    $return[$field] = $this->serialize($rawValue);
                    break;
                case $rawValue && isset($mapping['embedded']) && $mapping['type'] == 'many':
                    if (count($rawValue) == 0) {
                        break;
                    }
                    foreach ($rawValue as $embedDocument) {
                        $return[$field][] = $this->serialize($embedDocument);
                    }
                    break;
                case $rawValue && isset($mapping['reference']) && $mapping['type'] == 'one':
                    if ($this->nestingDepth < $this->maxNestingDepth) {
                        $this->nestingDepth++;
                        $referenceMetadata = $this->documentManager->getClassMetadata($mapping['targetDocument']);
                        $serializedDocument = $this->getReferenceSerializer($field, $metadata)->serialize(
                            $rawValue instanceof Proxy ?
                            $rawValue->{'get' . ucfirst($referenceMetadata->identifier)}() :
                            $referenceMetadata->reflFields[$referenceMetadata->identifier]->getValue($rawValue),
                            $mapping
                        );
                        if ($serializedDocument) {
                            $return[$field] = $serializedDocument;
                        }
                        $this->nestingDepth--;
                    }
                    break;
                case $rawValue && isset($mapping['reference']) && $mapping['type'] == 'many':
                    if (count($rawValue) == 0) {
                        break;
                    }
                    if ($this->nestingDepth < $this->maxNestingDepth) {
                        $this->nestingDepth++;
                        foreach ($rawValue->getMongoData() as $referenceDocument) {
                            $serializedDocument = $this->getReferenceSerializer($field, $metadata)->serialize(
                                is_array($referenceDocument) ? $referenceDocument['$id'] : (string) $referenceDocument,
                                $mapping
                            );
                            if ($serializedDocument) {
                                $return[$field][] = $serializedDocument;
                            }
                        }
                        $this->nestingDepth--;
                    }
                    break;
                case array_key_exists($mapping['type'], $this->typeSerializers):
                    $return[$field] = $this->getTypeSerializer($mapping['type'])->serialize($rawValue);
                    break;
                case $rawValue != null:
                    $return[$field] = $rawValue;
            }
        }
        return $return;
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
