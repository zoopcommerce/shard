<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer\Reference;

use Zoop\Shard\Core\ObjectManagerAwareInterface;
use Zoop\Shard\Core\ObjectManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

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

    public function serialize($id, array $mapping)
    {
        $document = $this->objectManager->getRepository($mapping['targetDocument'])->find($id);
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
