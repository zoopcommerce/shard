<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Owner;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\CreateEventArgs;
use Zoop\Shard\Core\MetadataSleepEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            CoreEvents::CREATE,
            CoreEvents::METADATA_SLEEP,
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Stamp\CreateEventArgs $eventArgs
     */
    public function create(CreateEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (isset($metadata->owner)) {
            $reflField = $metadata->reflFields[$metadata->owner];
            $owner = $reflField->getValue($document);
            if (! isset($owner)) {
                $reflField->setValue($document, $this->getUsername());
                $eventArgs->addRecompute($metadata->owner);
            }
        }
    }

    public function metadataSleep(MetadataSleepEventArgs $eventArgs)
    {
        if (isset($eventArgs->getMetadata()->owner)) {
            $eventArgs->addSerialized('owner');
        }
    }

    protected function getUsername()
    {
        if ($this->serviceLocator->has('user')) {
            return $this->serviceLocator->get('user')->getUsername();
        } else {
            return null;
        }
    }
}
