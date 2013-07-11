<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Generator;

use Zoop\Shard\AbstractExtension;

/**
 * Defines the resouces this extension requires
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Extension extends AbstractExtension {

    protected $resourceMap;

    protected $serviceManagerConfig = [
        'factories' => [
            'resourceMap' => 'Zoop\Shard\Generator\ResourceMapFactory',
        ]
    ];

    public function getResourceMap() {
        return $this->resourceMap;
    }

    public function setResourceMap($resourceMap) {
        $this->resourceMap = $resourceMap;
    }
}