<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Mystique\Result;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DocumentValidator implements DocumentValidatorInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    protected $currentDocument;

    protected $currentField;

    public function getCurrentDocument() {
        return $this->currentDocument;
    }

    public function getCurrentField() {
        return $this->currentField;
    }

    /**
     *
     * @param object $document
     * @return boolean
     */
    public function isValid($document, ClassMetadata $metadata, array $changeSet = null)
    {
        if (! isset($metadata->validator)) {
            return new Result(['value' => true]);
        }

        $result = new DocumentValidatorResult(['value' => true]);
        $this->currentDocument = $document;

        // Field level validators
        if (isset($metadata->validator['fields'])) {
            foreach ($metadata->validator['fields'] as $field => $validatorDefinition) {

                //if a change set is provided, only validate fields that have changed
                if (isset($changeSet) && ! isset($changeSet[$field])){
                    continue;
                }

                $this->currentField = $field;

                $validator = $this->getValidator($validatorDefinition);
                $value = $metadata->getFieldValue($document, $field);

                $validatorResult = $validator->isValid($value);

                $result->addFieldResult($field, $validatorResult);
                foreach ($validatorResult->getMessages() as $message) {
                    $result->addMessage($field . ': ' . $message);
                }
                if (! $validatorResult->getValue()) {
                    $result->setValue(false);
                }
            }
        }

        // Document level validators
        if (isset($metadata->validator['document'])) {
            $validator = $this->getValidator($metadata->validator['document']);
            $validatorResult = $validator->isValid($document);

            $result->addClassResult($validatorResult);
            foreach ($validatorResult->getMessages() as $message) {
                $result->addMessage('document: ' . $message);
            }
            if (! $validatorResult->getValue()) {
                $result->setValue(false);
            }
        }

        return $result;
    }

    protected function getValidator($validatorDefinition)
    {
        $class = $validatorDefinition['class'];
        if (isset($validatorDefinition['options'])) {
            $options = $validatorDefinition['options'];
        } else {
            $options = [];
        }

        if ($class == 'Zoop\Mystique\Chain') {
            $validators = [];
            foreach ($options['validators'] as $subDef) {
                $validators[] = $this->getValidator($subDef);
            }
            $options['validators'] = $validators;
        }

        if ($this->serviceLocator->has($class)) {
            $instance = $this->serviceLocator->get($class);
            foreach ($options as $key => $value) {
                $setter = 'set' . ucfirst($key);
                $instance->$setter($value);
            }
            return $instance;
        } else {
            return new $class($options);
        }
    }
}
