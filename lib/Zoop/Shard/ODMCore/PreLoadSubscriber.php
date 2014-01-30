<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\Common\EventSubscriber;
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
 */
class PreLoadSubscriber implements EventSubscriber
{
    /**
     *
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
     *
     * @param \Doctrine\ODM\MongoDB\Event\PreLoadEventArgs $eventArgs
     */
    public function preLoad(PreLoadEventArgs $eventArgs)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $document = $eventArgs->getDocument();
        $metadata = $documentManager->getClassMetadata(get_class($document));
        $eventManager = $documentManager->getEventManager();

        foreach ($metadata->associationMappings as $field => $mapping){
            if (!isset($mapping['embedded']) || !$mapping['embedded']) {
                continue;
            }

            $targetMetadata = $documentManager->getClassMetadata($mapping['targetDocument']);

            $readEventArgs = new ReadEventArgs($targetMetadata, $eventManager);
            $eventManager->dispatchEvent(CoreEvents::READ, $readEventArgs);

            if ($readEventArgs->getReject()){
                $eventArgs->getData()[$field] = null;
            }
        }
    }
}
