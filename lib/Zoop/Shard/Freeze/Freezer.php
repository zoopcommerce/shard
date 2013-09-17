<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Core\EventManagerTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Freezer implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use EventManagerTrait;

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
        if (!($field = $this->getFreezeField($metadata)) || $this->isFrozen($document, $metadata)) {
            //nothing to do
            return;
        }

        $eventManager = $this->getEventManager();

        // Raise preFreeze
        $freezerEventArgs = new FreezerEventArgs($document, $metadata, $eventManager);
        $eventManager->dispatchEvent(Events::PRE_FREEZE, $freezerEventArgs);
        if ($freezerEventArgs->getReject()) {
            return;
        }

        //do the freeze
        $metadata->setFieldValue($document, $field, true);

        //raise post freeze
        $eventManager->dispatchEvent(Events::POST_FREEZE, $freezerEventArgs);
    }

    public function thaw($document, ClassMetadata $metadata)
    {
        if (!($field = $this->getFreezeField($metadata)) || !$this->isFrozen($document, $metadata)) {
            //nothing to do
            return;
        }

        $eventManager = $this->getEventManager();

        // Raise preThaw
        $freezerEventArgs = new FreezerEventArgs($document, $metadata, $eventManager);
        $eventManager->dispatchEvent(Events::PRE_THAW, $freezerEventArgs);
        if ($freezerEventArgs->getReject()) {
            return;
        }

        $metadata->setFieldValue($document, $field, false);

        //raise post thaw
        $eventManager->dispatchEvent(Events::POST_THAW, $freezerEventArgs);
    }
}
