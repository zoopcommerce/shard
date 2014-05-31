<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\EventManager as BaseEventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Event\PreLoadEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Zoop\Shard\Core\AbstractChangeEventArgs;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\ChangeSet;
use Zoop\Shard\Core\ReadEventArgs;
use Zoop\Shard\Core\DeleteEventArgs;
use Zoop\Shard\Core\UpdateEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class PreLoadSubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            ODMEvents::preLoad,
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     * @param PreLoadEventArgs $eventArgs
     */
    public function preLoad(PreLoadEventArgs $eventArgs)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $document = $eventArgs->getDocument();
        $metadata = $documentManager->getClassMetadata(get_class($document));
        $eventManager = $documentManager->getEventManager();

        foreach ($metadata->associationMappings as $field => $mapping) {
            if (!isset($mapping['embedded']) || !$mapping['embedded']) {
                continue;
            }

            if (isset($mapping['discriminatorField'])) {
                $unhydratedDoc = $eventArgs->getData();
                if (!isset($unhydratedDoc[$field])) {
                    continue;
                }
                $unhydratedEmbeddedDoc = $unhydratedDoc[$field];
                foreach ($unhydratedEmbeddedDoc as $i => $embeddedDoc) {
                    $discriminatorFieldValue = $embeddedDoc[$mapping['discriminatorField']];
                    $embeddedClassName = $mapping['discriminatorMap'][$discriminatorFieldValue];
                    $targetMetadata = $documentManager->getClassMetadata($embeddedClassName);

                    $readEventArgs = $this->getReadEventArgs($targetMetadata, $eventManager);

                    if ($readEventArgs->getReject()) {
                        $eventArgs->getData()[$field][$i] = null;
                    }
                }
            } else {
                $targetMetadata = $documentManager->getClassMetadata($mapping['targetDocument']);
                $readEventArgs = $this->getReadEventArgs($targetMetadata, $eventManager);

                if ($readEventArgs->getReject()) {
                    $eventArgs->getData()[$field] = null;
                }
            }
        }
    }

    /**
     * @param ClassMetadata $metadata
     * @param BaseEventManager $eventManager
     * @return ReadEventArgs
     */
    protected function getReadEventArgs(ClassMetadata $metadata, BaseEventManager $eventManager)
    {
        $readEventArgs = new ReadEventArgs($metadata, $eventManager);
        $eventManager->dispatchEvent(CoreEvents::READ, $readEventArgs);
        return $readEventArgs;
    }
}
