<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Zone;

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
        'subscriber.zone.annotationsubscriber',
        'subscriber.zone.mainsubscriber'
    ];

    protected $serviceManagerConfig = [
        'invokables' => [
            'subscriber.zone.annotationsubscriber' => 'Zoop\Shard\Zone\AnnotationSubscriber',
            'subscriber.zone.mainsubscriber'       => 'Zoop\Shard\Zone\MainSubscriber'
        ]
    ];

    protected $dependencies = [
        'extension.annotation' => true,
    ];

    protected $readFilterInclude = [];

    protected $readFilterExclude = [];

    public function getReadFilterInclude() {
        return $this->readFilterInclude;
    }

    public function setReadFilterInclude(array $readFilterInclude = []) {
        $this->readFilterInclude = $readFilterInclude;
    }

    public function getReadFilterExclude() {
        return $this->readFilterExclude;
    }

    public function setReadFilterExclude(array $readFilterExclude = []) {
        $this->readFilterExclude = $readFilterExclude;
    }
}
