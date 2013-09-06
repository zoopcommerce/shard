<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Zoop\Shard\AbstractExtension;

/**
 * Defines the resouces this extension requires
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Extension extends AbstractExtension
{

    protected $filters = [
        'softDelete' => 'Zoop\Shard\SoftDelete\Filter\SoftDelete'
    ];

    protected $subscribers = [
        'subscriber.softdelete.mainsubscriber',
        'subscriber.softdelete.stampsubscriber',
        'subscriber.softdelete.annotationsubscriber',
        'subscriber.softdelete.softdeletesubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'softDeleter' => 'Zoop\Shard\SoftDelete\SoftDeleter',
            'subscriber.softdelete.mainsubscriber' => 'Zoop\Shard\SoftDelete\MainSubscriber',
            'subscriber.softdelete.stampsubscriber' => 'Zoop\Shard\SoftDelete\StampSubscriber',
            'subscriber.softdelete.annotationsubscriber' => 'Zoop\Shard\SoftDelete\AnnotationSubscriber',
            'subscriber.softdelete.softdeletesubscriber' => 'Zoop\Shard\SoftDelete\AccessControl\SoftDeleteSubscriber'
        ]
    ];

    protected $dependencies = [
        'extension.annotation' => true,
        'extension.odmcore'    => true,
    ];
}
