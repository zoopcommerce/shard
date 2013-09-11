<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Adds freeze and thaw stamps during persist
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class StampSubscriber implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::POST_FREEZE,
            Events::POST_THAW
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Freeze\FreezerEventArgs $eventArgs
     */
    public function postFreeze(FreezerEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (isset($metadata->freeze['frozenBy'])) {
            $metadata->setFieldValue($document, $metadata->freeze['frozenBy'], $this->getUsername());
        }
        if (isset($metadata->freeze['frozenOn'])) {
            $metadata->setFieldValue($document, $metadata->freeze['frozenOn'], time());
        }
    }

    /**
     *
     * @param \Zoop\Shard\Freeze\FreezerEventArgs $eventArgs
     */
    public function postThaw(FreezerEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (isset($metadata->freeze['thawedBy'])) {
            $metadata->setFieldValue($document, $metadata->freeze['thawedBy'], $this->getUsername());
        }
        if (isset($metadata->freeze['thawedOn'])) {
            $metadata->setFieldValue($document, $metadata->freeze['thawedOn'], time());
        }
    }

    protected function getUsername()
    {
        if ($this->serviceLocator->has('user')) {
            return $this->serviceLocator->get('user')->getUsername();
        } else {
            return null;
        }
    }
}
