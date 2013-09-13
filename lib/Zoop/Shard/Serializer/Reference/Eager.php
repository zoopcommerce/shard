<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer\Reference;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Core\ObjectManagerAwareInterface;
use Zoop\Shard\Core\ObjectManagerAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Eager implements ReferenceSerializerInterface, ServiceLocatorAwareInterface, ObjectManagerAwareInterface
{
    use ServiceLocatorAwareTrait;
    use ObjectManagerAwareTrait;

    protected $serializer;

    public function serialize($document)
    {
        if ($document) {
            return $this->getSerializer()->toArray($document);
        } else {
            return null;
        }
    }

    protected function getSerializer()
    {
        if (!isset($this->serializer)) {
            $this->serializer = $this->serviceLocator->get('serializer');
        }

        return $this->serializer;
    }
}
