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
class MainSubscriber extends AbstractAccessControlSubscriber {

    /**
     *
     * @return array
     */
    public function getSubscribedEvents(){
        return [
            ManifestEvents::onBootstrap,
            ODMEvents::onFlush
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
            if ( ! $accessController->areAllowed([Actions::create], null, $document)->getAllowed()) {

                //stop creation
                $metadata = $documentManager->getClassMetadata(get_class($document));

                if ($metadata->isEmbeddedDocument){
                    list($mapping, $parent) = $unitOfWork->getParentAssociation($document);
                    $parentMetadata = $documentManager->getClassMetadata(get_class($parent));
                    if ($mapping['type'] == 'many'){
                        $collection = $parentMetadata->reflFields[$mapping['fieldName']]->getValue($parent);
                        $collection->removeElement($document);
                        $unitOfWork->recomputeSingleDocumentChangeSet($parentMetadata, $parent);
                    } else {
                        $parentMetadata->reflFields[$mapping->field]->setValue($document, null);
                    }
                }
                $unitOfWork->detach($document);

                if ($eventManager->hasListeners(AccessControlEvents::createDenied)) {
                    $eventManager->dispatchEvent(
                        AccessControlEvents::createDenied,
                        new EventArgs($document, $documentManager, Actions::create)
                    );
                }
            }
        }

        //Check update permissions
        foreach ($unitOfWork->getScheduledDocumentUpdates() as $document) {

            $actions = [];

            //Assemble all the actions that require permission
            $changeSet = $unitOfWork->getDocumentChangeSet($document);
            foreach ($changeSet as $field => $change){
                $actions[] = Actions::update($field);
            }

            if (! $accessController->areAllowed($actions, null, $document)->getAllowed()) {

                $metadata = $documentManager->getClassMetadata(get_class($document));

                //roll back changes
                if (!isset($changeSet)){
                    $changeSet = $unitOfWork->getDocumentChangeSet($document);
                }
                foreach ($changeSet as $field => $change){
                    $metadata->reflFields[$field]->setValue($document, $change[0]);
                }

                //stop updates
                $unitOfWork->clearDocumentChangeSet(spl_object_hash($document));

                if ($eventManager->hasListeners(AccessControlEvents::updateDenied)) {
                    $eventManager->dispatchEvent(
                        AccessControlEvents::updateDenied,
                        new EventArgs($document, $documentManager, 'update')
                    );
                }
                continue;
            }
        }

        //Check delete permsisions
        foreach ($unitOfWork->getScheduledDocumentDeletions() as $document) {
            if ( ! $accessController->areAllowed([Actions::delete], null, $document)->getAllowed()) {
                //stop delete
                $documentManager->persist($document);

                if ($eventManager->hasListeners(AccessControlEvents::deleteDenied)) {
                    $eventManager->dispatchEvent(
                        AccessControlEvents::deleteDenied,
                        new EventArgs($document, $documentManager, Actions::delete)
                    );
                }
            }
        }
    }
}