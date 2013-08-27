<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Serializer\Reference;

use Zoop\Shard\DocumentManagerAwareInterface;
use Zoop\Shard\DocumentManagerAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class SimpleLazy implements ReferenceSerializerInterface, DocumentManagerAwareInterface
{
    use DocumentManagerAwareTrait;

    public function serialize($id, array $mapping)
    {
        return $this->documentManager->getClassMetadata($mapping['targetDocument'])->collection . '/' . $id;
    }
}
