<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Adds create and update stamps during persist
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
            Events::POST_SOFT_DELETE,
            Events::POST_RESTORE
        ];
    }

    /**
     *
     * @param \Zoop\Shard\SoftDelete\SoftDeleteEventArgs $eventArgs
     */
    public function postSoftDelete(SoftDeleteEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (isset($metadata->softDelete['deletedBy'])) {
            $metadata->setFieldValue($document, $metadata->softDelete['deletedBy'], $this->getUsername());
        }
        if (isset($metadata->softDelete['deletedOn'])) {
            $metadata->setFieldValue($document, $metadata->softDelete['deletedOn'], time());
        }
    }

    /**
     *
     * @param \Zoop\Shard\SoftDelete\SoftDeleteEventArgs $eventArgs
     */
    public function postRestore(SoftDeleteEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (isset($metadata->softDelete['restoredBy'])) {
            $metadata->setFieldValue($document, $metadata->softDelete['restoredBy'], $this->getUsername());
        }
        if (isset($metadata->softDelete['restoredOn'])) {
            $metadata->setFieldValue($document, $metadata->softDelete['restoredOn'], time());
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
