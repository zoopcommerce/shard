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
        $stampMetadata = $metadata->getStamp();

        if (isset($stampMetadata['createdBy'])) {
            $metadata->setFieldValue($document, $stampMetadata['createdBy'], $this->getUsername());
            $eventArgs->addRecompute($stampMetadata['createdBy']);
        }
        if (isset($stampMetadata['createdOn'])) {
            $metadata->setFieldValue($document, $stampMetadata['createdOn'], time());
            $eventArgs->addRecompute($stampMetadata['createdOn']);
        }
        if (isset($stampMetadata['updatedBy'])) {
            $metadata->setFieldValue($document, $stampMetadata['updatedBy'], $this->getUsername());
            $eventArgs->addRecompute($stampMetadata['updatedBy']);
        }
        if (isset($stampMetadata['updatedOn'])) {
            $metadata->setFieldValue($document, $stampMetadata['updatedOn'], time());
            $eventArgs->addRecompute($stampMetadata['updatedOn']);
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
        $stampMetadata = $metadata->getStamp();

        if (isset($stampMetadata['updatedBy'])) {
            $metadata->setFieldValue($document, $stampMetadata['updatedBy'], $this->getUsername());
            $eventArgs->addRecompute($stampMetadata['updatedBy']);
        }
        if (isset($stampMetadata['updatedOn'])) {
            $metadata->setFieldValue($document, $stampMetadata['updatedOn'], time());
            $eventArgs->addRecompute($stampMetadata['updatedOn']);
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
