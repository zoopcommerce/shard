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
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\ReadEventArgs;
use Zoop\Shard\Core\UpdateEventArgs;
use Zoop\Shard\Core\DeleteEventArgs;
use Zoop\Shard\Core\MetadataSleepEventArgs;

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
            CoreEvents::READ,
            CoreEvents::DELETE,
            CoreEvents::UPDATE
        ];
    }

    public function read(ReadEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $freezeMetadata = $metadata->getFreeze();

        if (!isset($freezeMetadata) || !$freezeMetadata['flag']) {
            return;
        }

        $readFilter = $this->serviceLocator->get('extension.freeze')->getReadFilter();

        if ($readFilter == Extension::READ_ALL) {
            return;
        } else if ($readFilter == Extension::READ_ONLY_FROZEN) {
            $criteria = [$freezeMetadata['flag'] => true];
        } else if ($readFilter == Extension::READ_ONLY_NOT_FROZEN) {
            $criteria = [$freezeMetadata['flag'] => false];
        }

        $eventArgs->addCriteria($criteria);
    }

    /**
     *
     * @param \Zoop\Shard\Core\UpdateEventArgs $eventArgs
     * @return type
     */
    public function update(UpdateEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $freezer = $this->getFreezer();
        $metadata = $eventArgs->getMetadata();

        if (! $freezer->getFreezeField($metadata) || !$freezer->isFrozen($document, $metadata)) {
            return;
        }

        $freezeMetadata = $metadata->getFreeze();

        $changeSet = $eventArgs->getChangeSet();
        $count = 0;
        array_walk(
            $freezeMetadata,
            function ($item) use ($changeSet, &$count) {
                if ($changeSet->hasField($item)) {
                    ++$count;
                }
            }
        );

        if (count($changeSet->getFieldNames()) == $count) {
            return;
        }

        // Updates to frozen models are not allowed. Roll them back
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

        // Deletions of frozen models are not allowed. Roll them back
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
}
