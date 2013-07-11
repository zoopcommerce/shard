<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Zoop\Shard\DocumentManagerAwareInterface;
use Zoop\Shard\DocumentManagerAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class SoftDeleter implements DocumentManagerAwareInterface
{

    use DocumentManagerAwareTrait;

    public function isSoftDeleted($document){
        $metadata = $this->documentManager->getClassMetadata(get_class($document));
        return $metadata->reflFields[$metadata->softDelete['flag']]->getValue($document);
    }

    public function softDelete($document){
        $metadata = $this->documentManager->getClassMetadata(get_class($document));
        $metadata->reflFields[$metadata->softDelete['flag']]->setValue($document, true);
    }

    public function restore($document){
        $metadata = $this->documentManager->getClassMetadata(get_class($document));
        $metadata->reflFields[$metadata->softDelete['flag']]->setValue($document, false);
    }
}
