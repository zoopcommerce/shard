<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State\AccessControl;

use Doctrine\Common\EventSubscriber;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class StatePermissionSubscriber implements EventSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Shard\Permission\State::EVENT];
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationStatePermission(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $metadata = $eventArgs->getMetadata();

        $config = [
            'factory' => 'Zoop\Shard\State\AccessControl\StatePermissionFactory',
            'options' => []
        ];

        if (is_array($annotation->states)) {
            $config['options']['states'] = $annotation->states;
        } else {
            $config['options']['states'] = [$annotation->states];
        }

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

        $permissionsMetadata = $metadata->getPermissions();
        $permissionsMetadata[] = $config;
        $metadata->setPermissions($permissionsMetadata);
    }
}
