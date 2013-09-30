<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\AccessControl\EventArgs as AccessControlEventArgs;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\ReadEventArgs;
use Zoop\Shard\Core\UpdateEventArgs;

/**
 * Emits soft delete events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    protected $softDeleter;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            CoreEvents::READ,
            CoreEvents::UPDATE,
        ];
    }

    public function read(ReadEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();
        $softDeleteMetadata = $metadata->getSoftDelete();

        if (!isset($softDeleteMetadata) || !$softDeleteMetadata['flag']) {
            return;
        }

        $readFilter = $this->serviceLocator->get('extension.softdelete')->getReadFilter();

        if ($readFilter == Extension::READ_ALL) {
            return;
        } elseif ($readFilter == Extension::READ_ONLY_SOFT_DELETED) {
            $criteria = [$softDeleteMetadata['flag'] => true];
        } elseif ($readFilter == Extension::READ_ONLY_NOT_SOFT_DELETED) {
            $criteria = [$softDeleteMetadata['flag'] => false];
        }

        $eventArgs->addCriteria($criteria);
    }

    public function update(UpdateEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $softDeleter = $this->getSoftDeleter();
        $metadata = $eventArgs->getMetadata();

        if (! $softDeleter->getSoftDeleteField($metadata) || !$softDeleter->isSoftDeleted($document, $metadata)) {
            return;
        }

        $softDeleteMetadata = $metadata->getSoftDelete();

        $changeSet = $eventArgs->getChangeSet();
        $count = 0;
        array_walk(
            $softDeleteMetadata,
            function ($item) use ($changeSet, &$count) {
                if ($changeSet->hasField($item)) {
                    ++$count;
                }
            }
        );

        if (count($changeSet->getFieldNames()) == $count) {
            return;
        }

        // Updates to softDeleted models are not allowed. Roll them back
        $eventArgs->setReject(true);

        // Raise frozenUpdateDenied
        $eventArgs->getEventManager()->dispatchEvent(
            Events::SOFT_DELETED_UPDATE_DENIED,
            new AccessControlEventArgs($document, 'softDelete')
        );
    }

    protected function getSoftDeleter()
    {
        if (! isset($this->softDeleter)) {
            $this->softDeleter = $this->serviceLocator->get('softDeleter');
        }

        return $this->softDeleter;
    }
}
