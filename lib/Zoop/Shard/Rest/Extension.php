<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Rest;

use Zoop\Shard\AbstractExtension;

/**
 * Defines the resouces this extension requires
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Extension extends AbstractExtension {

    protected $serviceManagerConfig = [
        'factories' => [
            'endpointmap' => 'Zoop\Shard\Rest\EndpointMapFactory'
        ]
    ];

    /**
     *
     * @var array
     */
    protected $dependencies = array(
        'extension.reference' => true
    );

    protected $endpointMap;

    public function getEndpointMap() {
        return $this->endpointMap;
    }

    public function setEndpointMap($endpointMap) {
        $this->endpointMap = $endpointMap;
    }
}
