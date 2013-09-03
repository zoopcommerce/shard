<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Zoop\Shard\Events as ManifestEvents;
use Zoop\Shard\AccessControl\Events as AccessControlEvents;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber extends AbstractAccessControlSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            ManifestEvents::ON_BOOTSTRAP,
            // @codingStandardsIgnoreStart
            ODMEvents::onFlush
            // @codingStandardsIgnoreEnd
        ];
    }

    public function onBootstrap()
    {
        $this->getAccessController()->enableReadFilter();
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();
        $eventManager = $documentManager->getEventManager();
        $accessController = $this->getAccessController();

        foreach ($unitOfWork->getScheduledDocumentInsertions() as $document) {

            //Check create permissions
            if (! $accessController->areAllowed([Actions::CREATE], null, $document)->getAllowed()) {

                //stop creation
                $metadata = $documentManager->getClassMetadata(get_class($document));

                if ($metadata->isEmbeddedDocument) {
                    list($mapping, $parent) = $unitOfWork->getParentAssociation($document);
                    $parentMetadata = $documentManager->getClassMetadata(get_class($parent));
                    if ($mapping['type'] == 'many') {
                        $collection = $parentMetadata->reflFields[$mapping['fieldName']]->getValue($parent);
                        $collection->removeElement($document);
                        $unitOfWork->recomputeSingleDocumentChangeSet($parentMetadata, $parent);
                    } else {
                        $parentMetadata->reflFields[$mapping['fieldName']]->setValue($document, null);
                    }
                }
                $unitOfWork->detach($document);

                if ($eventManager->hasListeners(AccessControlEvents::CREATE_DENIED)) {
                    $eventManager->dispatchEvent(
                        AccessControlEvents::CREATE_DENIED,
                        new EventArgs($document, $documentManager, Actions::CREATE)
                    );
                }
            }
        }

        //Check update permissions
        foreach ($unitOfWork->getScheduledDocumentUpdates() as $document) {

            $actions = [];

            //Assemble all the actions that require permission
            $changeSet = $unitOfWork->getDocumentChangeSet($document);

            if (count($changeSet) == 0) {
                continue;
            }

            foreach ($changeSet as $field => $change) {
                $actions[] = Actions::update($field);
            }

            if (! $accessController->areAllowed($actions, null, $document)->getAllowed()) {

                $metadata = $documentManager->getClassMetadata(get_class($document));

                //roll back changes
                if (!isset($changeSet)) {
                    $changeSet = $unitOfWork->getDocumentChangeSet($document);
                }
                foreach ($changeSet as $field => $change) {
                    $metadata->reflFields[$field]->setValue($document, $change[0]);
                }

                //stop updates
                $unitOfWork->clearDocumentChangeSet(spl_object_hash($document));

                if ($eventManager->hasListeners(AccessControlEvents::UPDATE_DENIED)) {
                    $eventManager->dispatchEvent(
                        AccessControlEvents::UPDATE_DENIED,
                        new EventArgs($document, $documentManager, 'update')
                    );
                }
                continue;
            }
        }

        //Check delete permsisions
        foreach ($unitOfWork->getScheduledDocumentDeletions() as $document) {
            if (! $accessController->areAllowed([Actions::DELETE], null, $document)->getAllowed()) {
                //stop delete
                $documentManager->persist($document);

                if ($eventManager->hasListeners(AccessControlEvents::DELETE_DENIED)) {
                    $eventManager->dispatchEvent(
                        AccessControlEvents::DELETE_DENIED,
                        new EventArgs($document, $documentManager, Actions::DELETE)
                    );
                }
            }
        }
    }
}
