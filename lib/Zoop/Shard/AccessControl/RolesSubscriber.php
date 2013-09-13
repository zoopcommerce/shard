<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\Common\EventSubscriber;
use Zoop\Common\User\RoleAwareUserInterface;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RolesSubscriber implements EventSubscriber
{
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
        $user = $eventArgs->getUser();

        if (isset($user) &&
            $user instanceof RoleAwareUserInterface
        ) {
            $eventArgs->setRoles(array_merge($eventArgs->getRoles(), $user->getRoles()));
        }
    }
}
