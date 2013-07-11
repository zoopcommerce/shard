<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Zone;

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
        'subscriber.zone.annotationsubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.zone.annotationsubscriber' => 'Zoop\Shard\Zone\AnnotationSubscriber'
        ]
    ];

    protected $filters = [
        'zone' => 'Zoop\Shard\Zone\Filter\Zone'
    ];

    protected $dependencies = [
        'extension.annotation' => true,
    ];
}
