<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Zoop\Shard\AccessControl\Events as AccessControlEvents;
use Zoop\Shard\Events as ManifestEvents;
use Zoop\Shard\ODMCore\Events as ODMCoreEvents;
use Zoop\Shard\ODMCore\CoreEventArgs;

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
            ODMCoreEvents::CREATE,
            ODMCoreEvents::DELETE,
            ODMCoreEvents::UPDATE,
        ];
    }

    public function onBootstrap()
    {
        $this->getAccessController()->enableReadFilter();
    }

    public function create(CoreEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();

        //Check create permissions
        if ($this->getAccessController()->areAllowed([Actions::CREATE], null, $document)->getAllowed()) {
            return;
        }

        $documentManager = $this->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();
        $eventManager = $documentManager->getEventManager();

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

        $eventManager->dispatchEvent(
            AccessControlEvents::CREATE_DENIED,
            new EventArgs($document, Actions::CREATE)
        );
    }

    public function update(CoreEventArgs $eventArgs)
    {
        //Check update permissions
        $document = $eventArgs->getDocument();
        $documentManager = $this->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();
        $eventManager = $documentManager->getEventManager();
        $actions = [];

        //Assemble all the actions that require permission
        $changeSet = $unitOfWork->getDocumentChangeSet($document);

        if (count($changeSet) == 0) {
            return;
        }

        foreach ($changeSet as $field => $change) {
            $actions[] = Actions::update($field);
        }

        if ($this->getAccessController()->areAllowed($actions, null, $document)->getAllowed()) {
            return;
        }

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

        $eventManager->dispatchEvent(
            AccessControlEvents::UPDATE_DENIED,
            new EventArgs($document, 'update')
        );
    }

    public function delete(CoreEventArgs $eventArgs)
    {
        //Check delete permsisions
        $document = $eventArgs->getDocument();


        if ($this->getAccessController()->areAllowed([Actions::DELETE], null, $document)->getAllowed()) {
            return;
        }

        //stop delete
        $documentManager = $this->getDocumentManager();
        $eventManager = $documentManager->getEventManager();

        $documentManager->persist($document);

        $eventManager->dispatchEvent(
            AccessControlEvents::DELETE_DENIED,
            new EventArgs($document, Actions::DELETE)
        );
    }
}
