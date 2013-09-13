<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer\Reference;

use Zoop\Shard\Core\ObjectManagerAwareInterface;
use Zoop\Shard\Core\ObjectManagerAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RefLazy implements ReferenceSerializerInterface, ObjectManagerAwareInterface
{
    use ObjectManagerAwareTrait;

    public function serialize($document)
    {
        $metadata = $this->objectManager->getClassMetadata(get_class($document));

        return [
            '$ref' => $metadata->collection . '/' . $metadata->getFieldValue($document, $metadata->getIdentifier())
        ];
    }
}
