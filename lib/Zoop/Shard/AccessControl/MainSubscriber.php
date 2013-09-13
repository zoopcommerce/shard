<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Zoop\Shard\AccessControl\Events as AccessControlEvents;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\CreateEventArgs;
use Zoop\Shard\Core\DeleteEventArgs;
use Zoop\Shard\Core\UpdateEventArgs;
use Zoop\Shard\Core\MetadataSleepEventArgs;

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
            CoreEvents::BOOTSTRAPPED,
            CoreEvents::CREATE,
            CoreEvents::DELETE,
            CoreEvents::UPDATE,
            CoreEvents::METADATA_SLEEP,
        ];
    }

    public function bootstrapped()
    {
        $this->getAccessController()->enableReadFilter();
    }

    public function create(CreateEventArgs $eventArgs)
    {
        if ($eventArgs->getReject()) {
            //don't do anything if the create has already been rejected
            return;
        }

        $document = $eventArgs->getDocument();

        //Check create permissions
        if ($this->getAccessController()
                ->areAllowed([Actions::CREATE], $eventArgs->getMetadata(), $document)
                ->getAllowed()
        ) {
            return;
        }

        $eventArgs->setReject(true);

        $eventArgs->getEventManager()->dispatchEvent(
            AccessControlEvents::CREATE_DENIED,
            new EventArgs($document, Actions::CREATE)
        );
    }

    public function update(UpdateEventArgs $eventArgs)
    {
        //Check update permissions
        $document = $eventArgs->getDocument();
        $actions = [];

        foreach (array_keys($eventArgs->getChangeSet()) as $field) {
            $actions[] = Actions::update($field);
        }

        if ($this->getAccessController()->areAllowed($actions, $eventArgs->getMetadata(), $document)->getAllowed()) {
            return;
        }

        $eventArgs->setReject(true);

        $eventArgs->getEventManager()->dispatchEvent(
            AccessControlEvents::UPDATE_DENIED,
            new EventArgs($document, 'update')
        );
    }

    public function delete(DeleteEventArgs $eventArgs)
    {
        //Check delete permsisions
        $document = $eventArgs->getDocument();

        if ($this->getAccessController()
                ->areAllowed([Actions::DELETE], $eventArgs->getMetadata(), $document)
                ->getAllowed()
        ) {
            return;
        }

        $eventArgs->setReject(true);

        $eventArgs->getEventManager()->dispatchEvent(
            AccessControlEvents::DELETE_DENIED,
            new EventArgs($document, Actions::DELETE)
        );
    }

    public function metadataSleep(MetadataSleepEventArgs $eventArgs)
    {
        if (isset($eventArgs->getMetadata()->accessConrol)) {
            $eventArgs->addSerialized('accessConrol');
        }
        if (isset($eventArgs->getMetadata()->permissions)) {
            $eventArgs->addSerialized('permissions');
        }
    }
}
