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
        'subscriber.accessControl.mainsubscriber',
        'subscriber.accessControl.annotationsubscriber',
        'subscriber.accessControl.basicPermissionSubscriber'
    ];

    protected $filters = [
        'readAccessControl' => 'Zoop\Shard\AccessControl\Filter\ReadAccessControl'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.accessControl.mainsubscriber' =>
                'Zoop\Shard\AccessControl\MainSubscriber',
            'subscriber.accessControl.annotationsubscriber' =>
                'Zoop\Shard\AccessControl\AnnotationSubscriber',
            'subscriber.accessControl.basicPermissionSubscriber' =>
                'Zoop\Shard\AccessControl\BasicPermissionSubscriber',
            'accessController' =>
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
