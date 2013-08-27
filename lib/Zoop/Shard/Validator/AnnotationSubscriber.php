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
    public function annotationAlphaValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationBooleanValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
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
            switch ($type){
                case EventType::DOCUMENT:
                    if (isset($metadata->validator['document'])) {
                        $validators[] = $metadata->validator['document'];
                    }
                    break;
                case EventType::FIELD:
                    if (isset($metadata->validator['fields'][$reflection->getName()])) {
                        $validators[] = $metadata->validator['fields'][$reflection->getName()];
                    }
                    break;
            }
        }

        switch ($type){
            case EventType::DOCUMENT:
                if (count($validators) == 0) {
                    $this->setDocumentValidator($eventArgs, null);
                } elseif (count($validators) == 1) {
                    $this->setDocumentValidator($eventArgs, $validators[0]);
                } elseif (count($validators) > 1) {
                    $this->setDocumentValidator(
                        $eventArgs,
                        ['class' => $annotation->class, 'options' => ['validators' => $validators]]
                    );
                }
                break;
            case EventType::FIELD:
                if (count($validators) == 0) {
                    $this->setFieldValidator($eventArgs, null);
                } elseif (count($validators) == 1) {
                    $this->setFieldValidator($eventArgs, $validators[0]);
                } elseif (count($validators) > 1) {
                    $this->setFieldValidator(
                        $eventArgs,
                        ['class' => $annotation->class, 'options' => ['validators' => $validators]]
                    );
                }
                break;
        }
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationCreditCardValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationCreditCardExpiryValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationCvvValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationDateValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationEmailValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
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
    public function annotationFloatValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
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
    public function annotationHexColorValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationIntValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
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
    public function annotationNotRequiredValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationPasswordValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
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
    public function annotationRequiredValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationSlugValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationStringValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $this->setFieldValidator($eventArgs, ['class' => $annotation->class]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        switch ($eventArgs->getEventType()){
            case EventType::DOCUMENT:
                $this->setDocumentValidator(
                    $eventArgs,
                    [
                        'class' => $annotation->class,
                        'options' => $annotation->options
                    ]
                );
                break;
                break;
            case EventType::FIELD:
                $this->setFieldValidator(
                    $eventArgs,
                    [
                        'class' => $annotation->class,
                        'options' => $annotation->options
                    ]
                );
                break;
        }
    }

    protected function setFieldValidator($eventArgs, $definition)
    {
        if ($eventArgs->getAnnotation()->value && isset($definition)) {
            $eventArgs->getMetadata()->validator['fields'][$eventArgs->getReflection()->getName()] = $definition;
        } else {
            unset($eventArgs->getMetadata()->validator['fields'][$eventArgs->getReflection()->getName()]);
        }
    }

    protected function setDocumentValidator($eventArgs, $definition)
    {
        if ($eventArgs->getAnnotation()->value && isset($definition)) {
            $eventArgs->getMetadata()->validator['document'] = $definition;
        } else {
            unset($eventArgs->getMetadata()->validator['document']);
        }
    }
}
