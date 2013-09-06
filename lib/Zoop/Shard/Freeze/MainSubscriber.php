<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\AccessControl\EventArgs as AccessControlEventArgs;
use Zoop\Shard\GetDocumentManagerTrait;
use Zoop\Shard\ODMCore\Events as ODMCoreEvents;
use Zoop\Shard\ODMCore\CoreEventArgs;

/**
 * Emits freeze events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use GetDocumentManagerTrait;

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
        ];
    }

    public function update(CoreEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $documentManager = $this->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();
        $eventManager = $documentManager->getEventManager();

        $metadata = $documentManager->getClassMetadata(get_class($document));
        if (! isset($metadata->freeze) || ! ($field = $metadata->freeze['flag'])) {
            return;
        }

        $changeSet = $unitOfWork->getDocumentChangeSet($document);
        $freezer = $this->getFreezer();

        if (! isset($changeSet[$field])) {
            if ($freezer->isFrozen($document)) {
                // Updates to frozen documents are not allowed. Roll them back
                $unitOfWork->clearDocumentChangeSet(spl_object_hash($document));

                // Raise frozenUpdateDenied
                $eventManager->dispatchEvent(
                    Events::FROZEN_UPDATE_DENIED,
                    new AccessControlEventArgs($document, 'update')
                );
                $eventArgs->setShortCircut(true);
                return;
            } else {
                return;
            }
        }

        if ($changeSet[$field][1]) {
            // Trigger freeze events

            // Raise preFreeze
            $eventManager->dispatchEvent(
                Events::PRE_FREEZE,
                new LifecycleEventArgs($document, $documentManager)
            );

            if ($freezer->isFrozen($document)) {
                // Raise postFreeze
                $eventManager->dispatchEvent(
                    Events::POST_FREEZE,
                    new LifecycleEventArgs($document, $documentManager)
                );
            } else {
                // Freeze has been rolled back
                $metadata = $documentManager->getClassMetadata(get_class($document));
                $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
            }

        } else {
            // Trigger thaw events

            // Raise preThaw
            $eventManager->dispatchEvent(
                Events::PRE_THAW,
                new LifecycleEventArgs($document, $documentManager)
            );

            if (! $freezer->isFrozen($document)) {
                // Raise postThaw
                $eventManager->dispatchEvent(
                    Events::POST_THAW,
                    new LifecycleEventArgs($document, $documentManager)
                );
            } else {
                // Thaw has been rolled back
                $metadata = $documentManager->getClassMetadata(get_class($document));
                $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
            }
        }
    }

    public function delete(CoreEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $documentManager = $this->getDocumentManager();
        $eventManager = $documentManager->getEventManager();
        $freezer = $this->getFreezer();

        $metadata = $documentManager->getClassMetadata(get_class($document));
        if (! isset($metadata->freeze) ||
            ! ($metadata->freeze['flag']) || ! $freezer->isFrozen($document)
        ) {
            return;
        }

        // Deletions of frozen documents are not allowed. Roll them back
        $documentManager->persist($document);

        // Raise frozenDeleteDenied
        $eventManager->dispatchEvent(
            Events::FROZEN_DELETE_DENIED,
            new AccessControlEventArgs($document, 'delete')
        );
        $eventArgs->setShortCircut(true);
    }

    protected function getFreezer()
    {
        if (! isset($this->freezer)) {
            $this->freezer = $this->serviceLocator->get('freezer');
        }
        return $this->freezer;
    }
}
