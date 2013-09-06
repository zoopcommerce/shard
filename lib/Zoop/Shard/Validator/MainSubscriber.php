<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\GetDocumentManagerTrait;
use Zoop\Shard\ODMCore\Events as ODMCoreEvents;
use Zoop\Shard\ODMCore\CoreEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use GetDocumentManagerTrait;

    /**
     *
     * @var \Zoop\Mystique\ValidatorInterface
     */
    protected $documentValidator;

    /**
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        $events = [ODMCoreEvents::VALIDATE];
        return $events;
    }

    public function validate(CoreEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $documentManager = $this->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();
        $documentValidator = $this->getDocumentValidator();

        $metadata = $documentManager->getClassMetadata(get_class($document));

        $result = $documentValidator->isValid($document, $metadata);
        if (! $result->getValue()) {

            // Updates to invalid documents are not allowed. Roll them back
            $unitOfWork->detach($document);

            $eventManager = $documentManager->getEventManager();

            // Raise INVALID_OBJECT
            $eventManager->dispatchEvent(
                Events::INVALID_OBJECT,
                new EventArgs($document, $result)
            );

            $eventArgs->setShortCircut(true);
        }
    }

    /**
     *
     * @return \Zoop\Mystique\ValidatorInterface
     */
    protected function getDocumentValidator()
    {
        if (! isset($this->documentValidator)) {
            $this->documentValidator = $this->serviceLocator->get('documentValidator');
        }
        return $this->documentValidator;
    }
}
