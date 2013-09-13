<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State\AccessControl;

use Zoop\Shard\AccessControl\AbstractAccessControlSubscriber;
use Zoop\Shard\Annotation\Annotations as Shard;
use Zoop\Shard\Annotation\AnnotationEventArgs;
use Zoop\Shard\State\TransitionEventArgs;
use Zoop\Shard\State\Events as Events;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class TransitionPermissionSubscriber extends AbstractAccessControlSubscriber
{
    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Shard\Permission\Transition::EVENT,
            Events::PRE_TRANSITION
        ];
    }

    /**
     *
     * @param \Zoop\Shard\Annotation\AnnotationEventArgs $eventArgs
     */
    public function annotationTransitionPermission(AnnotationEventArgs $eventArgs)
    {
        $annotation = $eventArgs->getAnnotation();
        $metadata = $eventArgs->getMetadata();

        $config = [
            'factory' => 'Zoop\Shard\State\AccessControl\TransitionPermissionFactory',
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

        $metadata->permissions[] = $config;
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function preTransition(TransitionEventArgs $eventArgs)
    {
        if (! ($accessController = $this->getAccessController())) {
            //Access control is not enabled
            return;
        }

        $document = $eventArgs->getDocument();
        $eventManager = $eventArgs->getEventManager();
        $action = $eventArgs->getTransition()->getAction();
        $metadata = $eventArgs->getMetadata();

        if (! $accessController->areAllowed([$action], $metadata, $document)->getAllowed()) {
            //stop transition
            $metadata
                ->reflFields[array_keys($metadata->state)[0]]
                ->setValue($document, $eventArgs->getTransition()->getFrom());

            $eventManager->dispatchEvent(
                Events::TRANSITION_DENIED,
                $eventArgs
            );
        }
    }
}
