<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

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
        'subscriber.accesscontrol.mainsubscriber',
        'subscriber.accesscontrol.annotationsubscriber',
        'subscriber.accesscontrol.basicPermissionSubscriber',
        'subscriber.accesscontrol.rolesSubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.accesscontrol.mainsubscriber' =>
                'Zoop\Shard\AccessControl\MainSubscriber',
            'subscriber.accesscontrol.annotationsubscriber' =>
                'Zoop\Shard\AccessControl\AnnotationSubscriber',
            'subscriber.accesscontrol.basicPermissionSubscriber' =>
                'Zoop\Shard\AccessControl\BasicPermissionSubscriber',
            'subscriber.accesscontrol.rolesSubscriber' =>
                'Zoop\Shard\AccessControl\RolesSubscriber',
            'accesscontroller' =>
                'Zoop\Shard\AccessControl\AccessController'
        ]
    ];

    /**
     *
     * @var array
     */
    protected $dependencies = [
        'extension.annotation' => true
    ];
}
