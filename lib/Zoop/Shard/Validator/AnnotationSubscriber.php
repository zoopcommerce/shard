<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;
use Zoop\Shard\Annotation\EventType;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AnnotationSubscriber implements EventSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        $events = [
            Shard\Validator\Alpha::EVENT,
            Shard\Validator\Boolean::EVENT,
            Shard\Validator\Chain::EVENT,
            Shard\Validator\CreditCard::EVENT,
            Shard\Validator\CreditCardExpiry::EVENT,
            Shard\Validator\Cvv::EVENT,
            Shard\Validator\Date::EVENT,
            Shard\Validator\Email::EVENT,
            Shard\Validator\Equal::EVENT,
            Shard\Validator\Float::EVENT,
            Shard\Validator\GreaterThan::EVENT,
            Shard\Validator\GreaterThanEqual::EVENT,
            Shard\Validator\HexColor::EVENT,
            Shard\Validator\Int::EVENT,
            Shard\Validator\Length::EVENT,
            Shard\Validator\NotRequired::EVENT,
            Shard\Validator\Password::EVENT,
            Shard\Validator\Regex::EVENT,
            Shard\Validator\Required::EVENT,
            Shard\Validator\Slug::EVENT,
            Shard\Validator\String::EVENT,
            Shard\Validator::EVENT,
        ];

        return $events;
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationChainValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $eventManager = $eventArgs->getEventManager();
        $type = $eventArgs->getEventType();
        $reflection = $eventArgs->getReflection();
        $metadata = $eventArgs->getMetadata();
        $validators = [];

        foreach ($annotation->value as $validatorAnnotation) {
            $validatorEventArgs = new AnnotationEventArgs(
                $metadata,
                $type,
                $validatorAnnotation,
                $reflection,
                $eventManager
            );
            $eventManager->dispatchEvent(
                $validatorAnnotation::EVENT,
                $validatorEventArgs
            );
            $validatorMetadata = $this->getValidatorMetadata($metadata);
            if ($type == EventType::DOCUMENT && isset($validatorMetadata['document'])) {
                $validators[] = $validatorMetadata['document'];
            } elseif (isset($validatorMetadata['fields'][$reflection->getName()])) {
                 $validators[] = $validatorMetadata['fields'][$reflection->getName()];
            }
        }

        if (count($validators) == 0) {
            $validator = null;
        } elseif (count($validators) == 1) {
            $validator = $validators[0];
        } elseif (count($validators) > 1) {
            $validator = ['class' => $annotation->class, 'options' => ['validators' => $validators]];
        }

        if ($type == EventType::DOCUMENT) {
            $this->setDocumentValidator($eventArgs, $validator);
        } else {
            $this->setFieldValidator($eventArgs, $validator);
        }
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationEqualValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)) {
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator(
            $eventArgs,
            [
                'class' => $annotation->class,
                'options' => $options
            ]
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationGreaterThanValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)) {
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator(
            $eventArgs,
            [
                'class' => $annotation->class,
                'options' => $options
            ]
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationGreaterThanEqualValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)) {
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator(
            $eventArgs,
            [
                'class' => $annotation->class,
                'options' => $options
            ]
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationLengthValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->min)) {
            $options['min'] = $annotation->min;
        }
        if (isset($annotation->max)) {
            $options['max'] = $annotation->max;
        }

        $this->setFieldValidator(
            $eventArgs,
            [
                'class' => $annotation->class,
                'options' => $options
            ]
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationLessThanValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)) {
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator(
            $eventArgs,
            [
                'class' => $annotation->class,
                'options' => $options
            ]
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationLessThanEqualValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)) {
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator(
            $eventArgs,
            [
                'class' => $annotation->class,
                'options' => $options
            ]
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationNotEqualValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)) {
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator(
            $eventArgs,
            [
                'class' => $annotation->class,
                'options' => $options
            ]
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationRegexValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->regex)) {
            $options['regex'] = $annotation->regex;
        }

        $this->setFieldValidator(
            $eventArgs,
            [
                'class' => $annotation->class,
                'options' => $options
            ]
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        if ($eventArgs->getEventType() == EventType::DOCUMENT) {
            $this->setDocumentValidator(
                $eventArgs,
                [
                    'class' => $annotation->class,
                    'options' => $annotation->options
                ]
            );
        } else {
            $this->setFieldValidator(
                $eventArgs,
                [
                    'class' => $annotation->class,
                    'options' => $annotation->options
                ]
            );
        }
    }

    protected function setFieldValidator($eventArgs, $definition)
    {
        $metadata = $eventArgs->getMetadata();
        $validatorMetadata = $this->getValidatorMetadata($metadata);
        if ($eventArgs->getAnnotation()->value && isset($definition)) {
            $validatorMetadata['fields'][$eventArgs->getReflection()->getName()] = $definition;
        } else {
            unset($validatorMetadata['fields'][$eventArgs->getReflection()->getName()]);
        }
        $metadata->setValidator($validatorMetadata);
    }

    protected function setDocumentValidator($eventArgs, $definition)
    {
        $metadata = $eventArgs->getMetadata();
        $validatorMetadata = $this->getValidatorMetadata($metadata);
        if ($eventArgs->getAnnotation()->value && isset($definition)) {
            $validatorMetadata['document'] = $definition;
        } else {
            unset($validatorMetadata['document']);
        }
        $metadata->setValidator($validatorMetadata);
    }

    protected function getValidatorMetadata($metadata)
    {
        if (!$metadata->hasProperty('validator')) {
            $metadata->addProperty('validator', true);
            $metadata->setValidator([]);
        }
        return $metadata->getValidator();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments)
    {
        $this->setFieldValidator($arguments[0], ['class' => $arguments[0]->getAnnotation()->class]);
    }
}
