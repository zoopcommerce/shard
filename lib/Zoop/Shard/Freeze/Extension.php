<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Zoop\Shard\AbstractExtension;

/**
 * Defines the resouces this extension requires
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Extension extends AbstractExtension
{
    protected $dependencies = [
        'extension.annotation' => true
    ];

    protected $subscribers = [
        'subscriber.freeze.mainsubscriber',
        'subscriber.freeze.stampsubscriber',
        'subscriber.freeze.annotationsubscriber',
        'subscriber.freeze.freezesubscriber'
    ];

    protected $filters = [
        'freeze' => 'Zoop\Shard\Freeze\Filter\Freeze'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'freezer' => 'Zoop\Shard\Freeze\Freezer',
            'subscriber.freeze.mainsubscriber' => 'Zoop\Shard\Freeze\MainSubscriber',
            'subscriber.freeze.stampsubscriber' => 'Zoop\Shard\Freeze\StampSubscriber',
            'subscriber.freeze.annotationsubscriber' => 'Zoop\Shard\Freeze\AnnotationSubscriber',
            'subscriber.freeze.freezesubscriber' => 'Zoop\Shard\Freeze\AccessControl\FreezeSubscriber'
        ]
    ];
}
