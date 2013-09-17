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

    const READ_ALL = 'readAll';

    const READ_ONLY_SOFT_DELETED = 'readOnlySoftDeleted';

    const READ_ONLY_NOT_SOFT_DELETED = 'readOnlyNotSoftDeleted';

    protected $subscribers = [
        'subscriber.softdelete.mainsubscriber',
        'subscriber.softdelete.stampsubscriber',
        'subscriber.softdelete.annotationsubscriber',
        'subscriber.softdelete.softdeletesubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.softdelete.mainsubscriber' => 'Zoop\Shard\SoftDelete\MainSubscriber',
            'subscriber.softdelete.stampsubscriber' => 'Zoop\Shard\SoftDelete\StampSubscriber',
            'subscriber.softdelete.annotationsubscriber' => 'Zoop\Shard\SoftDelete\AnnotationSubscriber',
            'subscriber.softdelete.softdeletesubscriber' => 'Zoop\Shard\SoftDelete\AccessControl\SoftDeleteSubscriber',
            'softDeleter' => 'Zoop\Shard\SoftDelete\SoftDeleter',
        ]
    ];

    protected $dependencies = [
        'extension.annotation' => true
    ];

    protected $readFilter = 'readAll';

    public function getReadFilter() {
        return $this->readFilter;
    }

    public function setReadFilter($readFilter) {
        $this->readFilter = (string) $readFilter;
    }
}
