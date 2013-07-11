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
class AnnotationSubscriber implements EventSubscriber {

    /**
     *
     * @return array
     */
    public function getSubscribedEvents(){
        $events = [
            Shard\Validator\Alpha::event,
            Shard\Validator\Boolean::event,
            Shard\Validator\Chain::event,
            Shard\Validator\CreditCard::event,
            Shard\Validator\CreditCardExpiry::event,
            Shard\Validator\Cvv::event,
            Shard\Validator\Date::event,
            Shard\Validator\EmailAddress::event,
            Shard\Validator\Equal::event,
            Shard\Validator\Float::event,
            Shard\Validator\GreaterThan::event,
            Shard\Validator\GreaterThanEqual::event,
            Shard\Validator\HexColor::event,
            Shard\Validator\Int::event,
            Shard\Validator\Length::event,
            Shard\Validator\NotRequired::event,
            Shard\Validator\Password::event,
            Shard\Validator\Regex::event,
            Shard\Validator\Required::event,
            Shard\Validator\Slug::event,
            Shard\Validator\String::event,
            Shard\Validator::event,
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
        foreach ($annotation->value as $validatorAnnotation){
            $validatorEventArgs = new AnnotationEventArgs(
                $metadata,
                $type,
                $validatorAnnotation,
                $reflection,
                $eventManager
            );
            $eventManager->dispatchEvent(
                $validatorAnnotation::event,
                $validatorEventArgs
            );
            switch ($type){
                case EventType::document:
                    if (isset($metadata->validator['document'])){
                        $validators[] = $metadata->validator['document'];
                    }
                    break;
                case EventType::field:
                    if (isset($metadata->validator['fields'][$reflection->getName()])){
                        $validators[] = $metadata->validator['fields'][$reflection->getName()];
                    }
                    break;
            }
        }

        switch ($type){
            case EventType::document:
                if (count($validators) == 0){
                    $this->setDocumentValidator($eventArgs, null);
                } else if (count($validators) == 1){
                    $this->setDocumentValidator($eventArgs, $validators[0]);
                } else if (count($validators) > 1){
                    $this->setDocumentValidator($eventArgs, ['class' => $annotation->class, 'options' => ['validators' => $validators]]);
                }
                break;
            case EventType::field:
                if (count($validators) == 0){
                    $this->setFieldValidator($eventArgs, null);
                } else if (count($validators) == 1){
                    $this->setFieldValidator($eventArgs, $validators[0]);
                } else if (count($validators) > 1){
                    $this->setFieldValidator($eventArgs, ['class' => $annotation->class, 'options' => ['validators' => $validators]]);
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
    public function annotationEmailAddressValidator(AnnotationEventArgs $eventArgs)
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
        if (isset($annotation->compareValue)){
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator($eventArgs, [
            'class' => $annotation->class,
            'options' => $options
        ]);
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
        if (isset($annotation->compareValue)){
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator($eventArgs, [
            'class' => $annotation->class,
            'options' => $options
        ]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationGreaterThanEqualValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)){
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator($eventArgs, [
            'class' => $annotation->class,
            'options' => $options
        ]);
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
        if (isset($annotation->min)){
            $options['min'] = $annotation->min;
        }
        if (isset($annotation->max)){
            $options['max'] = $annotation->max;
        }

        $this->setFieldValidator($eventArgs, [
            'class' => $annotation->class,
            'options' => $options
        ]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationLessThanValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)){
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator($eventArgs, [
            'class' => $annotation->class,
            'options' => $options
        ]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationLessThanEqualValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)){
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator($eventArgs, [
            'class' => $annotation->class,
            'options' => $options
        ]);
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationNotEqualValidator(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $options = [];
        if (isset($annotation->compareValue)){
            $options['compareValue'] = $annotation->compareValue;
        }

        $this->setFieldValidator($eventArgs, [
            'class' => $annotation->class,
            'options' => $options
        ]);
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
        if (isset($annotation->regex)){
            $options['regex'] = $annotation->regex;
        }

        $this->setFieldValidator($eventArgs, [
            'class' => $annotation->class,
            'options' => $options
        ]);
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
            case EventType::document:
                $this->setDocumentValidator($eventArgs, [
                    'class' => $annotation->class,
                    'options' => $annotation->options
                ]);
                break;
                break;
            case EventType::field:
                $this->setFieldValidator($eventArgs, [
                    'class' => $annotation->class,
                    'options' => $annotation->options
                ]);
                break;
        }
    }

    protected function setFieldValidator($eventArgs, $definition){
        if ($eventArgs->getAnnotation()->value && isset($definition)){
            $eventArgs->getMetadata()->validator['fields'][$eventArgs->getReflection()->getName()] = $definition;
        } else {
            unset($eventArgs->getMetadata()->validator['fields'][$eventArgs->getReflection()->getName()]);
        }
    }

    protected function setDocumentValidator($eventArgs, $definition){
        if ($eventArgs->getAnnotation()->value && isset($definition)){
            $eventArgs->getMetadata()->validator['document'] = $definition;
        } else {
            unset($eventArgs->getMetadata()->validator['document']);
        }
    }
}
