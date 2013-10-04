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
use Zoop\Shard\Core\ModelManagerAwareInterface;
use Zoop\Shard\Core\ModelManagerAwareTrait;

/**
 * Provides methods for serializing models
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Serializer implements ServiceLocatorAwareInterface, ModelManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use ModelManagerAwareTrait;

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
     * @param  object          $document
     * @param  DocumentManager $documentManager
     * @return array
     */
    public function toArray($document)
    {
        return $this->serialize($document);
    }

    /**
     *
     * @param  object          $document
     * @param  DocumentManager $documentManager
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
        $serializerMetadata = $metadata->getSerializer();

        if (isset($serializerMetadata['fields'][$field]['serializeIgnore']) &&
            $serializerMetadata['fields'][$field]['serializeIgnore']
        ) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param  object | array          $document
     * @param  DocumentManager         $documentManager
     * @return array
     * @throws \BadMethodCallException
     */
    protected function serialize($document)
    {
        $return = [];

        $metadata = $this->modelManager->getClassMetadata(get_class($document));

        if ($metadata->hasDiscriminator()) {
            $return[$metadata->discriminatorField['name']] = $metadata->discriminatorValue;
        }

        if ($document instanceof Proxy) {
            try {
                $document->__load();
            } catch (\Exception $ex) {
                //Document may not be found due to access control
                return;
            }
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
            return $this->serializeSingleModel($metadata, $value, $field);
        } elseif ($metadata->hasAssociation($field)) {
            return $this->serializeCollection($metadata, $value, $field);
        } else {
            return $this->serializeSingleValue($metadata, $value, $field);
        }
    }

    protected function serializeSingleModel(ClassMetadata $metadata, $value, $field)
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
            $serializedDocument = $this->getReferenceSerializer($field, $metadata)->serialize($value);
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
        $result = [];

        if (isset($mapping['embedded'])) {
            foreach ($value as $index => $embedDocument) {
                $result = $this->serializeCollectionItem($embedDocument, $index, $mapping, $this, $result);
            }
        } else {
            if ($this->nestingDepth < $this->maxNestingDepth) {
                $this->nestingDepth++;
                foreach ($value as $index => $referenceDocument) {
                    $result = $this->serializeCollectionItem(
                        $referenceDocument,
                        $index,
                        $mapping,
                        $this->getReferenceSerializer($field, $metadata),
                        $result
                    );
                }
                $this->nestingDepth--;
            }
        }

        if (count($result) == 0) {
            return;
        }

        return $result;
    }

    protected function serializeCollectionItem($document, $index, $mapping, $serializer, array $result)
    {
        if (! $serializedDocument = $serializer->serialize($document)) {
            return $result;
        }

        if (isset($mapping['discriminatorMap'])) {
            $serializedDocument[$mapping['discriminatorField']] =
                array_search(get_class($document), $mapping['discriminatorMap']);
        }
        if ($mapping['strategy'] == 'set') {
            $targetMetadata = $this->modelManager->getClassMetadata(get_class($document));
            if (isset($serializedDocument[$targetMetadata->getIdentifier()])) {
                $key = $serializedDocument[$targetMetadata->getIdentifier()];
                unset($serializedDocument[$targetMetadata->getIdentifier()]);
                $result[$key] = $serializedDocument;
            } else {
                $result[$index] = $serializedDocument;
            }
        } else {
            $result[] = $serializedDocument;
        }

        return $result;
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
        $serializerMetadata = $metadata->getSerializer();

        if (isset($serializerMetadata['fields'][$field]['referenceSerializer'])) {
            $name = $serializerMetadata['fields'][$field]['referenceSerializer'];
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
