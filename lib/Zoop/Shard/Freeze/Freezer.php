<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Freezer
{
    public function getFreezeField(ClassMetadata $metadata)
    {
        if (isset($metadata->freeze) && isset($metadata->freeze['flag'])) {
            return $metadata->freeze['flag'];
        }
    }

    public function isFrozen($document, ClassMetadata $metadata)
    {
        return $metadata->getFieldValue($document, $metadata->freeze['flag']);
    }

    public function freeze($document, ClassMetadata $metadata)
    {
        $metadata->setFieldValue($document, $metadata->freeze['flag'], true);
    }

    public function thaw($document, ClassMetadata $metadata)
    {
        $metadata->setFieldValue($document, $metadata->freeze['flag'], false);
    }
}
