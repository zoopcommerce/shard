<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class SoftDeleter
{
    public function getSoftDeleteField(ClassMetadata $metadata)
    {
        if (isset($metadata->softDelete) && isset($metadata->softDelete['flag'])) {
            return $metadata->softDelete['flag'];
        }
    }

    public function isSoftDeleted($document, ClassMetadata $metadata)
    {
        return $metadata->reflFields[$metadata->softDelete['flag']]->getValue($document);
    }

    public function softDelete($document, ClassMetadata $metadata)
    {
        $metadata->reflFields[$metadata->softDelete['flag']]->setValue($document, true);
    }

    public function restore($document, ClassMetadata $metadata)
    {
        $metadata->reflFields[$metadata->softDelete['flag']]->setValue($document, false);
    }
}
