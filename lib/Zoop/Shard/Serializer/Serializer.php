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
 * Provides static methods for serializing documents
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Serializer implements ServiceLocatorAwareInterface, DocumentManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use DocumentManagerAwareTrait;

    const IGNORE_WHEN_UNSERIALIZING = 'ignore_when_unserializing';
    const IGNORE_WHEN_SERIALIZING = 'ignore_when_serializing';
    const IGNORE_ALWAYS = 'ignore_always';
    const IGNORE_NEVER = 'ignore_never';

    const UNSERIALIZE_UPDATE = 'unserialize_update';
    const UNSERIALIZE_PATCH = 'unserliaze_patch';

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

    protected function removeClassNameAndDiscriminatorFromArray(
        array $array,
        $classNameField,
        $discriminatorField = null
    ) {
        if (isset($array[$classNameField])) {
            unset($array[$classNameField]);
        }

        if (isset($array[$discriminatorField])) {
            unset($array[$discriminatorField]);
        }

        return $array;
    }

    public function fieldListForUnserialize(ClassMetadata $classMetadata)
    {
        $return = [];

        foreach ($classMetadata->fieldMappings as $field => $mapping) {
            if ($this->isUnserializableField($field, $classMetadata)) {
                $return[] = $field;
            }
        }

        return $return;
    }

    public function isUnserializableField($field, ClassMetadata $classMetadata)
    {
        if (isset($classMetadata->serializer['fields'][$field]['ignore']) &&
            (
                $classMetadata->serializer['fields'][$field]['ignore'] == self::IGNORE_WHEN_UNSERIALIZING ||
                $classMetadata->serializer['fields'][$field]['ignore'] == self::IGNORE_ALWAYS
            )
        ) {
            return false;
        }
        return true;
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
        if (isset($classMetadata->serializer['fields'][$field]['ignore']) &&
            (
                $classMetadata->serializer['fields'][$field]['ignore'] == self::IGNORE_WHEN_SERIALIZING ||
                $classMetadata->serializer['fields'][$field]['ignore'] == self::IGNORE_ALWAYS
            )
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
        $className = null,
        $mode = self::UNSERIALIZE_PATCH,
        $document = null
    ) {
        return $this->unserialize($data, $className, $mode, $document);
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
        $className = null,
        $mode = self::UNSERIALIZE_PATCH,
        $document = null
    ) {
        return $this->unserialize(json_decode($data, true), $className, $mode, $document);
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
        $className = null,
        $mode = self::UNSERIALIZE_PATCH,
        $document = null,
        $discriminatorField = null,
        $discriminatorMap = null
    ) {

        $documentManager = $this->documentManager;
        
        if (isset($discriminatorField) && isset($data[$discriminatorField])) {
            $metadata = $this->documentManager
                ->getClassMetadata($discriminatorMap[$data[$discriminatorField]]);
        } else {
            if (! isset($className)) {
                $className = $data[$this->classNameField];
            }

            if (! isset($className) || ! class_exists($className)) {
                throw new Exception\ClassNotFoundException(sprintf('ClassName %s could not be loaded', $className));
            }

            $metadata = $this->documentManager->getClassMetadata($className);

            // Check for discrimnator and discriminator field in data
            if (isset($metadata->discriminatorField) && isset($data[$metadata->discriminatorField['fieldName']])) {
                $metadata = $this->documentManager
                    ->getClassMetadata($metadata->discriminatorMap[$data[$metadata->discriminatorField['fieldName']]]);
            }
        }

        // Check for reference
        if (isset($data['$ref'])) {
            $pieces = explode('/', $data['$ref']);
            $id = $pieces[count($pieces) - 1];
            return $documentManager->getReference($className, $id);
        }

        // Attempt to load prexisting document from db
        if (! isset($document) && isset($data[$metadata->identifier])) {
            $document = $documentManager
                ->createQueryBuilder()
                ->find($metadata->name)
                ->field($metadata->identifier)->equals($data[$metadata->identifier])
                ->getQuery()
                ->getSingleResult();
        }
        if (isset($document)) {
            $newInstance = false;
        } else {
            $newInstance = true;
            $document = $metadata->newInstance();
        }

        foreach ($this->fieldListForUnserialize($metadata) as $field) {

            if ($field == $metadata->identifier && !$newInstance) {
                continue;
            }

            $mapping = $metadata->fieldMappings[$field];
            unset($value);

            switch (true){
                case isset($mapping['embedded']) && $mapping['type'] == 'one' && isset($data[$field]):
                    $value = $this->unserialize(
                        $data[$field],
                        $mapping['targetDocument'],
                        $mode,
                        $metadata->reflFields[$field]->getValue($document)
                    );
                    break;
                case isset($mapping['embedded']) && $mapping['type'] == 'many':
                    $newArray = [];
                    if (isset($data[$field])) {
                        if (! ($embeddedCollection = $metadata->reflFields[$field]->getValue($document))) {
                            $embeddedCollection = new ArrayCollection;
                        }
                        foreach ($data[$field] as $index => $embeddedData){
                            $embeddedCollection[$index] = $this->unserialize(
                                $embeddedData,
                                isset($mapping['targetDocument']) ? $mapping['targetDocument'] : null,
                                $mode,
                                $embeddedCollection[$index],
                                isset($mapping['discriminatorField']) ? $mapping['discriminatorField'] : null,
                                isset($mapping['discriminatorMap']) ? $mapping['discriminatorMap'] : null
                            );
                        }
                        $value = $embeddedCollection;
                        break;
                    }
                    switch ($mode) {
                        case self::UNSERIALIZE_PATCH:
                            if ($metadata->reflFields[$field]->getValue($document) == null) {
                                $value = new ArrayCollection([]);
                            }
                            break;
                        case self::UNSERIALIZE_UPDATE:
                            $value = new ArrayCollection([]);
                            break;
                    }
                    break;
                case isset($mapping['reference']) && $mapping['type'] == 'one' && isset($data[$field]):
                    if (isset($data[$field]['$ref'])) {
                        $pieces = explode('/', $data[$field]['$ref']);
                        $id = $pieces[count($pieces) - 1];
                        $value = $documentManager->getReference($mapping['targetDocument'], $id);
                    } elseif (is_array($data[$field])) {
                        $value = $this->unserialize(
                            $data[$field],
                            isset($mapping['targetDocument']) ? $mapping['targetDocument'] : null,
                            $mode,
                            null,
                            isset($mapping['discriminatorField']) ? $mapping['discriminatorField'] : null,
                            isset($mapping['discriminatorMap']) ? $mapping['discriminatorMap'] : null
                        );
                    } else {
                        $value = $documentManager->getReference($mapping['targetDocument'], $data[$field]);
                    }
                    break;
                case isset($mapping['reference']) && $mapping['type'] == 'many':
                    $newArray = [];
                    if (isset($data[$field])) {
                        foreach ($data[$field] as $value) {

                            //extract id for a reference, otherwise, unserialize array
                            unset($id);
                            if (is_array($value)) {
                                if (isset($value['$ref'])) {
                                    $pieces = explode('/', $value['$ref']);
                                    $id = $pieces[count($pieces) - 1];
                                } else {
                                    $value = $this->removeClassNameAndDiscriminatorFromArray(
                                        $value,
                                        $this->classNameField
                                    );
                                    $identifier = $documentManager
                                        ->getClassMetadata($mapping['targetDocument'])
                                        ->identifier;

                                    if (count($value) == 1 && isset($value[$identifier])) {
                                        $id = $value[$identifier];
                                    }
                                }
                            } else {
                                $id = $value;
                            }

                            if (isset($id)) {
                                $newArray[] = $documentManager->getReference($mapping['targetDocument'], $id);
                            } else {
                                $newArray[] = $this->unserialize(
                                    $value,
                                    $mapping['targetDocument'],
                                    $mode
                                );
                            }
                        }
                        $value = new ArrayCollection($newArray);
                        break;
                    }
                    switch ($mode) {
                        case self::UNSERIALIZE_PATCH:
                            if ($metadata->reflFields[$field]->getValue($document) == null) {
                                $value = new ArrayCollection([]);
                            }
                            break;
                        case self::UNSERIALIZE_UPDATE:
                            $value = new ArrayCollection([]);
                            break;
                    }
                    break;
                case array_key_exists($mapping['type'], $this->typeSerializers) && isset($data[$field]):
                    $value = $this->getTypeSerializer($mapping['type'])->unserialize($data[$field]);
                    break;
                case $mapping['type'] == 'float' && isset($data[$field]) && is_integer($data[$field]):
                    $value = (float) $data[$field];
                    break;
                case isset($data[$field]):
                    $value = $data[$field];
                    break;
            }
            if (isset($value)) {
                $metadata->reflFields[$field]->setValue($document, $value);
            } elseif ($mode == self::UNSERIALIZE_UPDATE) {
                $metadata->reflFields[$field]->setValue($document, null);
            }
        }

        return $document;
    }
}
