<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Owner;

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
        'subscriber.owner.mainsubscriber',
        'subscriber.owner.annotationsubscriber',
        'subscriber.owner.rolessubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.owner.mainsubscriber' => 'Zoop\Shard\Owner\MainSubscriber',
            'subscriber.owner.annotationsubscriber' => 'Zoop\Shard\Owner\AnnotationSubscriber',
            'subscriber.owner.rolessubscriber' => 'Zoop\Shard\Owner\RolesSubscriber'
        ]
    ];

    protected $dependencies = [
        'extension.annotation' => true
    ];
}
