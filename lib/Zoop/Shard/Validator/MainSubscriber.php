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
use Zoop\Shard\ODMCore\Events as ODMCoreEvents;
use Zoop\Shard\ODMCore\CoreEventArgs;
use Zoop\Shard\ODMCore\MetadataSleepEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{
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
    public function getSubscribedEvents()
    {
        $events = [
            ODMCoreEvents::VALIDATE,
            ODMCoreEvents::METADATA_SLEEP,
        ];
        return $events;
    }

    public function validate(CoreEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $documentValidator = $this->getDocumentValidator();

        $result = $documentValidator->isValid($document);
        if (! $result->getValue()) {

            // Raise INVALID_OBJECT
            $eventArgs->getEventManager()->dispatchEvent(
                Events::INVALID_OBJECT,
                new EventArgs($document, $result)
            );

            $eventArgs->setReject(true);
        }
    }

    public function metadataSleep(MetadataSleepEventArgs $eventArgs){
        $eventArgs->addSerialized('validator');
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
