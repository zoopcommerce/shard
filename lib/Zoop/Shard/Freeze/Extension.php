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
    const READ_ALL = 'readAll';

    const READ_ONLY_FROZEN = 'readOnlyFrozen';

    const READ_ONLY_NOT_FROZEN = 'readOnlyNotFrozen';

    protected $subscribers = [
        'subscriber.freeze.mainsubscriber',
        'subscriber.freeze.stampsubscriber',
        'subscriber.freeze.annotationsubscriber',
        'subscriber.freeze.freezesubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.freeze.mainsubscriber' => 'Zoop\Shard\Freeze\MainSubscriber',
            'subscriber.freeze.stampsubscriber' => 'Zoop\Shard\Freeze\StampSubscriber',
            'subscriber.freeze.annotationsubscriber' => 'Zoop\Shard\Freeze\AnnotationSubscriber',
            'subscriber.freeze.freezesubscriber' => 'Zoop\Shard\Freeze\AccessControl\FreezeSubscriber',
            'freezer' => 'Zoop\Shard\Freeze\Freezer',
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
