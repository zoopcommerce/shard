<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Annotation;

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
        'subscriber.annotation.mainsubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.annotation.mainsubscriber' => 'Zoop\Shard\Annotation\MainSubscriber',
        ],
    ];
}
