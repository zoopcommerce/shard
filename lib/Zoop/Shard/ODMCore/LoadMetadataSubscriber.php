<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\LoadMetadataEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class LoadMetadataSubscriber implements EventSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            ODMEvents::loadClassMetadata,
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        $documentManager = $eventArgs->getDocumentManager();
        $eventManager = $documentManager->getEventManager();

        if (! isset($this->annotationReader)) {
            $this->annotationReader = new AnnotationReader;
            $this->annotationReader = new CachedReader(
                $this->annotationReader,
                $documentManager->getConfiguration()->getMetadataCacheImpl()
            );
        }

        //Inherit document annotations from parent classes
        $parentMetadata = [];
        if (count($metadata->parentClasses) > 0) {
            foreach ($metadata->parentClasses as $parentClass) {
                $parentMetadata[] = $documentManager->getClassMetadata($parentClass);
            }
        }

        $eventManager->dispatchEvent(
            CoreEvents::LOAD_METADATA,
            new LoadMetadataEventArgs($metadata, $parentMetadata, $this->annotationReader, $eventManager)
        );
    }
}
