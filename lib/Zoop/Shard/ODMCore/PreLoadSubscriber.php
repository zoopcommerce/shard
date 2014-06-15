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
     * @SuppressWarnings(PHPMD.LongVariable)
     * @param PreLoadEventArgs $eventArgs
     */
    public function preLoad(PreLoadEventArgs $eventArgs)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $document = $eventArgs->getDocument();
        $metadata = $documentManager->getClassMetadata(get_class($document));

        foreach ($metadata->associationMappings as $field => $mapping) {
            if (isset($mapping['embedded']) && !!$mapping['embedded']) {
                if (isset($mapping['discriminatorField'])) {
                    $this->preLoadEmbeddedWithDiscriminator($eventArgs, $field, $mapping);
                } else {
                    $this->preLoadEmbeddedWithoutDiscriminator($eventArgs, $field, $mapping);
                }
            }
        }
    }

    /**
     *
     * @param PreLoadEventArgs $eventArgs
     * @param type $field
     * @param type $mapping
     */
    protected function preLoadEmbeddedWithoutDiscriminator(PreLoadEventArgs $eventArgs, $field, $mapping)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $eventManager = $documentManager->getEventManager();

        $targetMetadata = $documentManager->getClassMetadata($mapping['targetDocument']);
        $readEventArgs = $this->getReadEventArgs($targetMetadata, $eventManager);

        if ($readEventArgs->getReject()) {
            $eventArgs->getData()[$field] = null;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     *
     * @param PreLoadEventArgs $eventArgs
     * @param string $field
     * @param array $mapping
     */
    protected function preLoadEmbeddedWithDiscriminator(PreLoadEventArgs $eventArgs, $field, $mapping)
    {
        $unhydratedDoc = $eventArgs->getData();

        if (isset($unhydratedDoc[$field])) {
            $documentManager = $eventArgs->getDocumentManager();
            $eventManager = $documentManager->getEventManager();

            if ($mapping['type'] === 'one') {
                $discriminatorFieldValue = $unhydratedDoc[$field][$mapping['discriminatorField']];
                $embeddedClassName = $mapping['discriminatorMap'][$discriminatorFieldValue];
                $targetMetadata = $documentManager->getClassMetadata($embeddedClassName);
                $readEventArgs = $this->getReadEventArgs($targetMetadata, $eventManager);

                if ($readEventArgs->getReject()) {
                    $eventArgs->getData()[$field] = null;
                }
            } else {
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
