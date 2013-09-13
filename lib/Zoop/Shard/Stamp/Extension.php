<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Stamp;

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
        'subscriber.stamp.mainsubscriber',
        'subscriber.stamp.annotationsubscriber',
        'subscriber.stamp.rolessubscriber',
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.stamp.mainsubscriber' => 'Zoop\Shard\Stamp\MainSubscriber',
            'subscriber.stamp.annotationsubscriber' => 'Zoop\Shard\Stamp\AnnotationSubscriber',
            'subscriber.stamp.rolessubscriber' => 'Zoop\Shard\Stamp\RolesSubscriber',
        ]
    ];

    protected $dependencies = [
        'extension.annotation' => true
    ];
}
