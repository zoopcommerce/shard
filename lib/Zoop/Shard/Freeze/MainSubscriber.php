<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\AccessControl\EventArgs as AccessControlEventArgs;
use Zoop\Shard\ODMCore\Events as ODMCoreEvents;
use Zoop\Shard\ODMCore\UpdateEventArgs;
use Zoop\Shard\ODMCore\DeleteEventArgs;
use Zoop\Shard\ODMCore\MetadataSleepEventArgs;

/**
 * Emits freeze events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $freezer;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            ODMCoreEvents::DELETE,
            ODMCoreEvents::UPDATE,
            ODMCoreEvents::METADATA_SLEEP,
        ];
    }

    /**
     *
     * @param \Zoop\Shard\ODMCore\UpdateEventArgs $eventArgs
     * @return type
     */
    public function update(UpdateEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $freezer = $this->getFreezer();
        $metadata = $eventArgs->getMetadata();

        if (! ($field = $freezer->getFreezeField($metadata)) || !$freezer->isFrozen($document, $metadata)) {
            return;
        }

        $changeSet = $eventArgs->getChangeSet();
        $count = 0;
        array_walk($metadata->freeze,
            function ($item) use ($changeSet, &$count) {
                if (array_key_exists($item, $changeSet)) {
                    ++$count;
                }
            }
        );

        if (count($changeSet) == $count){
            return;
        }

        // Updates to frozen documents are not allowed. Roll them back
        $eventArgs->setReject(true);

        // Raise frozenUpdateDenied
        $eventArgs->getEventManager()->dispatchEvent(
            Events::FROZEN_UPDATE_DENIED,
            new AccessControlEventArgs($document, 'update')
        );
    }

    public function delete(DeleteEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();
        $freezer = $this->getFreezer();

        if (! $freezer->getFreezeField($metadata) || ! $freezer->isFrozen($document, $metadata)) {
            return;
        }

        // Deletions of frozen documents are not allowed. Roll them back
        $eventArgs->setReject(true);

        // Raise frozenDeleteDenied
        $eventArgs->getEventManager()->dispatchEvent(
            Events::FROZEN_DELETE_DENIED,
            new AccessControlEventArgs($document, 'delete')
        );
    }

    protected function getFreezer()
    {
        if (! isset($this->freezer)) {
            $this->freezer = $this->serviceLocator->get('freezer');
        }
        return $this->freezer;
    }

    public function metadataSleep(MetadataSleepEventArgs $eventArgs){
        $eventArgs->addSerialized('freeze');
    }
}
