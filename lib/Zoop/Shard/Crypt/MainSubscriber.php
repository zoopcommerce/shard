<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Crypt\BlockCipher\BlockCipherServiceInterface;
use Zoop\Shard\Crypt\Hash\HashServiceInterface;
use Zoop\Shard\GetDocumentManagerTrait;
use Zoop\Shard\ODMCore\Events as ODMCoreEvents;
use Zoop\Shard\ODMCore\UpdateEventArgs;

/**
 * Listener hashes fields marked with CryptHash annotation
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;
    use GetDocumentManagerTrait;

    protected $hashHelper;

    protected $blockCipherHelper;

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            // @codingStandardsIgnoreStart
            ODMEvents::prePersist,
            // @codingStandardsIgnoreEnd
            ODMCoreEvents::UPDATE,
        ];
    }

    public function getHashHelper()
    {
        if (! isset($this->hashHelper)) {
            $this->hashHelper = $this->serviceLocator->get('crypt.hashhelper');
        }
        return $this->hashHelper;
    }

    public function getBlockCipherHelper()
    {
        if (!isset($this->blockCipherHelper)) {
            $this->blockCipherHelper = $this->serviceLocator->get('crypt.blockcipherhelper');
        }
        return $this->blockCipherHelper;
    }

    public function update(UpdateEventArgs $eventArgs)
    {
        $documentManager = $this->getDocumentManager();
        $document = $eventArgs->getDocument();
        $metadata = $documentManager->getClassMetadata(get_class($document));

        $requireRecompute = false;
        if ($this->hashFields($metadata, $document)){
            $requireRecompute = true;
        }
        if ($this->blockCipherFields($metadata, $document)){
            $requireRecompute = true;
        }

        if ($requireRecompute) {
            $documentManager->getUnitOfWork()->recomputeSingleDocumentChangeSet($metadata, $document);
        }
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $document = $eventArgs->getDocument();
        $documentManager = $eventArgs->getDocumentManager();
        $metadata = $documentManager->getClassMetadata(get_class($document));

        $this->getHashHelper()->hashDocument($document, $metadata);
        $this->getBlockCipherHelper()->encryptDocument($document, $metadata);

    }

    protected function hashFields($metadata, $document)
    {
        if (! isset($metadata->crypt['hash'])){
            return;
        }

        foreach ($metadata->crypt['hash'] as $field => $setting){

            $changeSet = $this->getDocumentManager()
                ->getUnitOfWork()
                ->getDocumentChangeSet($document);

            if ($this->hasChanged($field, $changeSet)) {
                $service = $this->serviceLocator->get($setting['service']);
                if (! $service instanceof HashServiceInterface) {
                    throw new \Zoop\Shard\Exception\InvalidArgumentException();
                }
                $service->hashField($field, $document, $metadata);
                return true;
            }
        }
    }

    protected function blockCipherFields($metadata, $document)
    {
        if (! isset($metadata->crypt['blockCipher'])){
            return;
        }

        foreach ($metadata->crypt['blockCipher'] as $field => $setting){

            $changeSet = $this->getDocumentManager()
                ->getUnitOfWork()
                ->getDocumentChangeSet($document);

            if ($this->hasChanged($field, $changeSet)) {
                $service = $this->serviceLocator->get($setting['service']);
                if (! $service instanceof BlockCipherServiceInterface) {
                    throw new \Zoop\Shard\Exception\InvalidArgumentException();
                }
                $service->encryptField($field, $document, $metadata);
                return true;
            }
        }
    }

    protected function hasChanged($field, $changeSet){

        list($old, $new) = $changeSet[$field];

        // Check for change
        if (($old == null || $old == $new) ||
            (! isset($new) || $new == '')
        ) {
            return false;
        }

        return true;
    }
}
