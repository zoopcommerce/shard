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
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\AbstractChangeEventArgs;
use Zoop\Shard\Core\MetadataSleepEventArgs;

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
            CoreEvents::VALIDATE,
            CoreEvents::METADATA_SLEEP,
        ];

        return $events;
    }

    public function validate(AbstractChangeEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $documentValidator = $this->getDocumentValidator();

        $result = $documentValidator->isValid($document, $eventArgs->getMetadata(), $eventArgs->getChangeSet());
        if (! $result->getValue()) {

            // Raise INVALID_MODEL
            $eventArgs->getEventManager()->dispatchEvent(
                Events::INVALID_MODEL,
                new EventArgs($document, $result)
            );

            $eventArgs->setReject(true);
        }
    }

    public function metadataSleep(MetadataSleepEventArgs $eventArgs)
    {
        if (isset($eventArgs->getMetadata()->validator)) {
            $eventArgs->addSerialized('validator');
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
