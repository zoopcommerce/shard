<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State;

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
        'subscriber.state.mainsubscriber',
        'subscriber.state.annotationsubscriber',
        'subscirber.state.statePermissionSubscirber',
        'subscirber.state.transitionPermissionSubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.state.mainsubscriber' =>
                'Zoop\Shard\State\MainSubscriber',
            'subscriber.state.annotationsubscriber' =>
                'Zoop\Shard\State\AnnotationSubscriber',
            'subscirber.state.statePermissionSubscirber' =>
                'Zoop\Shard\State\AccessControl\StatePermissionSubscriber',
            'subscirber.state.transitionPermissionSubscriber' =>
                'Zoop\Shard\State\AccessControl\TransitionPermissionSubscriber'
        ]
    ];

    protected $filters = [
        'state' => 'Zoop\Shard\State\Filter\State'
    ];

    protected $dependencies = [
        'extension.annotation' => true
    ];
}
