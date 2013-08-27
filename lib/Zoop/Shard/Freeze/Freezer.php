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
        return $metadata->reflFields[$metadata->freeze['flag']]->getValue($document);
    }

    public function freeze($document)
    {
        $metadata = $this->documentManager->getClassMetadata(get_class($document));
        $metadata->reflFields[$metadata->freeze['flag']]->setValue($document, true);
    }

    public function thaw($document)
    {
        $metadata = $this->documentManager->getClassMetadata(get_class($document));
        $metadata->reflFields[$metadata->freeze['flag']]->setValue($document, false);
    }
}
