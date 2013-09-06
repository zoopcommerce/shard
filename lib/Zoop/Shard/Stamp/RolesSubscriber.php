<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Stamp;

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
    const CREATOR = 'creator';
    const UPDATER = 'updater';

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
            isset($username) &&
            isset($metadata->stamp)
        ) {
            if (isset($metadata->stamp['createdBy']) &&
                $metadata->getFieldValue($document, $metadata->stamp['createdBy']) == $username
            ) {
                $eventArgs->addRole(self::CREATOR);
            }

            if (isset($metadata->stamp['updatedBy']) &&
                $metadata->getFieldValue($document, $metadata->stamp['updatedBy']) == $username                         
            ) {
                $eventArgs->addRole(self::UPDATER);
            }
        }
    }
}
