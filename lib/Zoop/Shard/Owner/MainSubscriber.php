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

        if (! ($ownerField = $metadata->getOwner())) {
            return;
        }

        $owner = $metadata->getFieldValue($document, $ownerField);
        if (! isset($owner)) {
            $metadata->setFieldValue($document, $ownerField, $this->getUsername());
            $eventArgs->addRecompute($ownerField);
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
