<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer;

use Zoop\Shard\AbstractExtension;

/**
 * Defines the resouces this extension requires
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Extension extends AbstractExtension
{
    protected $subscribers = [
        'subscriber.serializer.annotation'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.serializer.annotation' => 'Zoop\Shard\Serializer\AnnotationSubscriber',
            'serializer.reference.refLazy'     => 'Zoop\Shard\Serializer\Reference\RefLazy',
            'serializer.reference.simpleLazy'  => 'Zoop\Shard\Serializer\Reference\SimpleLazy',
            'serializer.reference.eager'       => 'Zoop\Shard\Serializer\Reference\Eager',
            'serializer.type.dateToISO8601'    => 'Zoop\Shard\Serializer\Type\DateToISO8601',
            'serializer.type.dateToTimestamp'  => 'Zoop\Shard\Serializer\Type\DateToTimestamp'
        ],
        'factories' => [
            'serializer'   => 'Zoop\Shard\Serializer\SerializerFactory',
            'unserializer' => 'Zoop\Shard\Serializer\UnserializerFactory',
        ]
    ];

    /** @var array */
    protected $dependencies = [
        'extension.annotation' => true
    ];

    /** @var array */
    protected $typeSerializers = [
        'date' => 'serializer.type.dateToISO8601'
    ];

    /** @var int */
    protected $maxNestingDepth = 1;

    protected $classNameField = '_className';

    public function getTypeSerializers()
    {
        return $this->typeSerializers;
    }

    public function setTypeSerializers(array $typeSerializers)
    {
        $this->typeSerializers = $typeSerializers;
    }

    public function getMaxNestingDepth()
    {
        return $this->maxNestingDepth;
    }

    public function setMaxNestingDepth($maxNestingDepth)
    {
        $this->maxNestingDepth = (integer) $maxNestingDepth;
    }

    public function getClassNameField()
    {
        return $this->classNameField;
    }

    public function setClassNameField($classNameField)
    {
        $this->classNameField = (string) $classNameField;
    }
}
