<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

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
        'subscriber.validator.mainsubscriber',
        'subscriber.validator.annotationsubscriber'
    ];

    protected $dependencies = [
        'extension.annotation' => true,
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'documentvalidator' => 'Zoop\Shard\Validator\DocumentValidator',
            'subscriber.validator.mainsubscriber' => 'Zoop\Shard\Validator\MainSubscriber',
            'subscriber.validator.annotationsubscriber' => 'Zoop\Shard\Validator\AnnotationSubscriber'
        ]
    ];
}
