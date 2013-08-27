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
        'subscriber.owner.annotationsubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.owner.mainsubscriber' => 'Zoop\Shard\Owner\MainSubscriber',
            'subscriber.owner.annotationsubscriber' => 'Zoop\Shard\Owner\AnnotationSubscriber'
        ]
    ];

    protected $dependencies = [
        'extension.annotation' => true
    ];
}
