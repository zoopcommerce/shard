<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer\Reference;

use Zoop\Shard\Core\ModelManagerAwareInterface;
use Zoop\Shard\Core\ModelManagerAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class SimpleLazy implements ReferenceSerializerInterface, ModelManagerAwareInterface
{
    use ModelManagerAwareTrait;

    public function serialize($document)
    {
        $metadata = $this->modelManager->getClassMetadata(get_class($document));

        return $metadata->collection . '/' . $metadata->getFieldValue($document, $metadata->getIdentifier());
    }
}
