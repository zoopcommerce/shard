<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Stamp;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\CreateEventArgs;
use Zoop\Shard\Core\MetadataSleepEventArgs;
use Zoop\Shard\Core\UpdateEventArgs;

/**
 * Adds create and update stamps during persist
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
            CoreEvents::UPDATE,
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

        if (isset($metadata->stamp['createdBy'])) {
            $metadata->setFieldValue($document, $metadata->stamp['createdBy'], $this->getUsername());
            $eventArgs->addRecompute($metadata->stamp['createdBy']);
        }
        if (isset($metadata->stamp['createdOn'])) {
            $metadata->setFieldValue($document, $metadata->stamp['createdOn'], time());
            $eventArgs->addRecompute($metadata->stamp['createdOn']);
        }
        if (isset($metadata->stamp['updatedBy'])) {
            $metadata->setFieldValue($document, $metadata->stamp['updatedBy'], $this->getUsername());
            $eventArgs->addRecompute($metadata->stamp['updatedBy']);
        }
        if (isset($metadata->stamp['updatedOn'])) {
            $metadata->setFieldValue($document, $metadata->stamp['updatedOn'], time());
            $eventArgs->addRecompute($metadata->stamp['updatedOn']);
        }
    }

    /**
     *
     * @param \Zoop\Shard\Stamp\UpdateEventArgs $eventArgs
     */
    public function update(UpdateEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if (isset($metadata->stamp['updatedBy'])) {
            $metadata->setFieldValue($document, $metadata->stamp['updatedBy'], $this->getUsername());
            $eventArgs->addRecompute($metadata->stamp['updatedBy']);
        }
        if (isset($metadata->stamp['updatedOn'])) {
            $metadata->setFieldValue($document, $metadata->stamp['updatedOn'], time());
            $eventArgs->addRecompute($metadata->stamp['updatedOn']);
        }
    }

    public function metadataSleep(MetadataSleepEventArgs $eventArgs){
        $eventArgs->addSerialized('stamp');
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
