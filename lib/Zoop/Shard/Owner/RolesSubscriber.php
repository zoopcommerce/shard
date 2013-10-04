<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Owner;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\AccessControl\Events;
use Zoop\Shard\AccessControl\GetRolesEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RolesSubscriber implements EventSubscriber
{
    const OWNER = 'owner';

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::GET_ROLES
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function getRoles(GetRolesEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $metadata = $eventArgs->getMetadata();

        if ($user = $eventArgs->getUser()) {
            $username = $user->getUsername();
        }

        $ownerField = $metadata->getOwner();

        if (isset($document) &&
            $ownerField &&
            isset($username) &&
            $metadata->getFieldValue($document, $ownerField) == $username
        ) {
            $eventArgs->addRole(self::OWNER);
        }
    }
}
