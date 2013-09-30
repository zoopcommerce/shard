<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class BasicPermissionSubscriber implements EventSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Shard\Permission\Basic::EVENT
        );
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationBasicPermission(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();

        $config = [
            'factory' => 'Zoop\Shard\AccessControl\BasicPermissionFactory',
            'options' => []
        ];

        if (isset($annotation->roles)) {
            if (is_array($annotation->roles)) {
                $config['options']['roles'] = $annotation->roles;
            } else {
                $config['options']['roles'] = [$annotation->roles];
            }
        } else {
            $config['options']['roles'] = [];
        }

        if (isset($annotation->allow)) {
            if (is_array($annotation->allow)) {
                $config['options']['allow'] = $annotation->allow;
            } else {
                $config['options']['allow'] = [$annotation->allow];
            }
        } else {
            $config['options']['allow'] = [];
        }

        if (isset($annotation->deny)) {
            if (is_array($annotation->deny)) {
                $config['options']['deny'] = $annotation->deny;
            } else {
                $config['options']['deny'] = [$annotation->deny];
            }
        } else {
            $config['options']['deny'] = [];
        }

        $metadata = $eventArgs->getMetadata();
        $permissionsMetadata = $metadata->getPermissions();
        $permissionsMetadata[] = $config;
        $metadata->setPermissions($permissionsMetadata);
    }
}
