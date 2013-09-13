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

        if (isset($document) &&
            isset($metadata->owner) &&
            isset($username) &&
            $metadata->getFieldValue($document, $metadata->owner) == $username
        ) {
            $eventArgs->addRole(self::OWNER);
        }
    }
}
