<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Core\EventManagerTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class SoftDeleter implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use EventManagerTrait;
    
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
        if (!($field = $this->getSoftDeleteField($metadata)) || $this->isSoftDeleted($document, $metadata)) {
            //nothing to do
            return;
        }

        $eventManager = $this->getEventManager();

        // Raise preSoftDelete
        $softDeleteEventArgs = new SoftDeleteEventArgs($document, $metadata, $eventManager);
        $eventManager->dispatchEvent(Events::PRE_SOFT_DELETE, $softDeleteEventArgs);
        if ($softDeleteEventArgs->getReject()) {
            return;
        }

        //do the soft delete
        $metadata->setFieldValue($document, $field, true);

        //raise postSoftDelete
        $eventManager->dispatchEvent(Events::POST_SOFT_DELETE, $softDeleteEventArgs);
    }

    public function restore($document, ClassMetadata $metadata)
    {
        if (!($field = $this->getSoftDeleteField($metadata)) || !$this->isSoftDeleted($document, $metadata)) {
            //nothing to do
            return;
        }

        $eventManager = $this->getEventManager();

        // Raise preRestore
        $softDeleteEventArgs = new SoftDeleteEventArgs($document, $metadata, $eventManager);
        $eventManager->dispatchEvent(Events::PRE_RESTORE, $softDeleteEventArgs);
        if ($softDeleteEventArgs->getReject()) {
            return;
        }

        $metadata->setFieldValue($document, $field, false);

        //raise postRestore
        $eventManager->dispatchEvent(Events::POST_RESTORE, $softDeleteEventArgs);
    }
}
