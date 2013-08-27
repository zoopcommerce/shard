<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Rest;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class EndpointMap
{
    protected $map = [];

    public function setMap($map)
    {
        $this->map = $map;
    }

    public function hasEndpoint($name)
    {
        return array_key_exists($name, $this->map);
    }

    public function getEndpoint($name)
    {
        if (! $this->hasEndpoint($name)) {
            return;
        }
        $endpoint = $this->map[$name];
        if (! $endpoint instanceof Endpoint) {
            $endpoint = new Endpoint($endpoint);
            $endpoint->setName($name);
            $this->map[$name] = $endpoint;
        }
        return $endpoint;
    }

    public function getEndpointsFromClass($class)
    {
        $result = [];

        $checkEmbeddedEndpoints = function ($endpoint) use (&$result, $class, &$checkEmbeddedEndpoints) {
            $embeddedLists = $endpoint->getEmbeddedLists();
            if (count($embeddedLists) > 0) {
                foreach ($embeddedLists as $name => $embeddedEndpoint) {
                    if ($embeddedEndpoint->getClass() == $class) {
                        $result[] = $embeddedEndpoint;
                    }
                    $checkEmbeddedEndpoints($embeddedEndpoint);
                }
            }
        };

        foreach ($this->map as $name => $endpoint) {
            $endpoint = $this->getEndpoint($name);
            if ($endpoint->getClass() == $class) {
                $result[] = $endpoint;
            }
            $checkEmbeddedEndpoints($endpoint);
        }

        return $result;
    }
}
