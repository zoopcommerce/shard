<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    /**
     *
     * @var \Zoop\Mystique\ValidatorInterface
     */
    protected $documentValidator;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents(){
        $events = [
            ODMEvents::onFlush
        ];
        return $events;
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs  $eventArgs)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();
        $documentValidator = $this->getDocumentValidator();

        foreach ($unitOfWork->getScheduledDocumentUpdates() as $document) {
            $metadata = $documentManager->getClassMetadata(get_class($document));

            $result = $documentValidator->isValid($document, $metadata);
            if ( ! $result->getValue()) {

                // Updates to invalid documents are not allowed. Roll them back
                $unitOfWork->detach($document);

                $eventManager = $documentManager->getEventManager();

                // Raise invalidUpdate
                if ($eventManager->hasListeners(Events::invalidUpdate)) {
                    $eventManager->dispatchEvent(
                        Events::invalidUpdate,
                        new EventArgs($document, $documentManager, $result)
                    );
                }
            }
        }

        foreach ($unitOfWork->getScheduledDocumentInsertions() as $document) {
            $metadata = $documentManager->getClassMetadata(get_class($document));

            $result = $documentValidator->isValid($document, $metadata);
            if ( ! $result->getValue()) {

                //stop creation
                $unitOfWork->detach($document);

                $eventManager = $documentManager->getEventManager();

                // Raise invalidCreate
                if ($eventManager->hasListeners(Events::invalidCreate)) {
                    $eventManager->dispatchEvent(
                        Events::invalidCreate,
                        new EventArgs($document, $documentManager, $result)
                    );
                }
            }
        }
    }

    /**
     *
     * @return \Zoop\Mystique\ValidatorInterface
     */
    protected function getDocumentValidator() {
        if ( !isset($this->documentValidator)){
            $this->documentValidator = $this->serviceLocator->get('documentValidator');
        }
        return $this->documentValidator;
    }
}
