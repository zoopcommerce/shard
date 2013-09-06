<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Zoop\Shard\DocumentManagerAwareInterface;
use Zoop\Shard\DocumentManagerAwareTrait;
use Zoop\Shard\Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Provides methods for unserializing documents
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Unserializer implements ServiceLocatorAwareInterface, DocumentManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use DocumentManagerAwareTrait;

    const UNSERIALIZE_UPDATE = 'unserialize_update';
    const UNSERIALIZE_PATCH = 'unserliaze_patch';

    /** @var array */
    protected $typeSerializers = [];

    protected $classNameField;

    public function setTypeSerializers(array $typeSerializers)
    {
        $this->typeSerializers = $typeSerializers;
    }

    public function setClassNameField($classNameField)
    {
        $this->classNameField = (string) $classNameField;
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
        if (isset($classMetadata->serializer['fields'][$field]['unserializeIgnore']) &&
            $classMetadata->serializer['fields'][$field]['unserializeIgnore']
        ) {
            return false;
        }
        return true;
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
                        foreach ($data[$field] as $index => $embeddedData) {
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
                            if ($embeddedCollection = $metadata->reflFields[$field]->getValue($document)) {
                                foreach ($embeddedCollection as $key => $item) {
                                    $embeddedCollection->remove($key);
                                }
                                $value = $embeddedCollection;
                            } else {
                                $value = new ArrayCollection([]);
                            }
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
                    if (isset($data[$field])) {
                        if (! ($referenceCollection = $metadata->reflFields[$field]->getValue($document))) {
                            $referenceCollection = new ArrayCollection;
                        }
                        foreach ($data[$field] as $index => $referenceData) {

                            //extract id for a reference, otherwise, unserialize array
                            unset($id);
                            if (is_array($referenceData)) {
                                if (isset($referenceData['$ref'])) {
                                    $pieces = explode('/', $referenceData['$ref']);
                                    $id = $pieces[count($pieces) - 1];
                                } else {
                                    $referenceData = $this->removeClassNameAndDiscriminatorFromArray(
                                        $referenceData,
                                        $this->classNameField
                                    );
                                    $identifier = $documentManager
                                        ->getClassMetadata($mapping['targetDocument'])
                                        ->identifier;

                                    if (count($referenceData) == 1 && isset($referenceData[$identifier])) {
                                        $id = $referenceData[$identifier];
                                    }
                                }
                            } else {
                                $id = $referenceData;
                            }

                            if (isset($id)) {
                                if (method_exists($referenceCollection, 'getMongoData') && $id !== $referenceCollection->getMongoData()[$index]['$id']) {
                                    $referenceCollection[$index] =
                                        $documentManager->getReference($mapping['targetDocument'], $id);
                                }
                            } else {
                                $referenceCollection[$index] = $this->unserialize(
                                    $referenceData,
                                    $mapping['targetDocument'],
                                    $mode,
                                    $referenceCollection[$index]
                                );
                            }
                        }
                        $value = $referenceCollection;
                        break;
                    }
                    switch ($mode) {
                        case self::UNSERIALIZE_PATCH:
                            if ($metadata->reflFields[$field]->getValue($document) == null) {
                                $value = new ArrayCollection([]);
                            }
                            break;
                        case self::UNSERIALIZE_UPDATE:
                            if ($referenceCollection = $metadata->reflFields[$field]->getValue($document)) {
                                foreach ($referenceCollection as $key => $item) {
                                    $referenceCollection->remove($key);
                                }
                                $value = $referenceCollection;
                            } else {
                                $value = new ArrayCollection([]);
                            }
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

    protected function getTypeSerializer($type)
    {
        return $this->serviceLocator->get($this->typeSerializers[$type]);
    }
}
