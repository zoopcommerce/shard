<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Zoop\Shard\DocumentManagerAwareInterface;
use Zoop\Shard\DocumentManagerAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Freezer implements DocumentManagerAwareInterface
{
    use DocumentManagerAwareTrait;

    public function isFrozen($document)
    {
        $metadata = $this->documentManager->getClassMetadata(get_class($document));
        return $metadata->getFieldValue($document, $metadata->freeze['flag']);
    }

    public function freeze($document)
    {
        $metadata = $this->documentManager->getClassMetadata(get_class($document));
        $metadata->setFieldValue($document, $metadata->freeze['flag'], true);
    }

    public function thaw($document)
    {
        $metadata = $this->documentManager->getClassMetadata(get_class($document));
        $metadata->setFieldValue($document, $metadata->freeze['flag'], false);
    }
}
