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

    /**
     *
     * @param  object  $document
     * @return boolean
     */
    public function isValid($document, ClassMetadata $metadata, array $changeSet = null)
    {
        $result = new DocumentValidatorResult(['value' => true]);

        if (! isset($metadata->validator)) {
            return $result;
        }

        // Field level validators
        if (isset($metadata->validator['fields'])) {
            foreach ($metadata->validator['fields'] as $field => $validatorDefinition) {

                //if a change set is provided, only validate fields that have changed
                if (isset($changeSet) && ! isset($changeSet[$field])) {
                    continue;
                }

                $result->addFieldResult(
                    $field,
                    $this->getValidator($validatorDefinition)->isValid(
                        $metadata->getFieldValue($document, $field)
                    )
                );
            }
        }

        // Document level validators
        if (isset($metadata->validator['document'])) {
            $result->addClassResult(
                $this->getValidator($metadata->validator['document'])->isValid($document)
            );
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

        return new $class($options);
    }
}
