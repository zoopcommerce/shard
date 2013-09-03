<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Events as ODMEvents;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Crypt\BlockCipher\BlockCipherServiceInterface;
use Zoop\Shard\Crypt\Hash\HashServiceInterface;

/**
 * Listener hashes fields marked with CryptHash annotation
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class MainSubscriber implements EventSubscriber, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

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
            ODMEvents::onFlush
            // @codingStandardsIgnoreEnd
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

    /**
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $documentManager = $eventArgs->getDocumentManager();
        $unitOfWork = $documentManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledDocumentUpdates() as $document) {
            $changeSet = $unitOfWork->getDocumentChangeSet($document);
            $metadata = $documentManager->getClassMetadata(get_class($document));
            foreach ($changeSet as $field => $change) {
                $old = $change[0];
                $new = $change[1];

                // Check for change
                if ($old == null || $old == $new) {
                    continue;
                }

                if (! isset($new) || $new == '') {
                    continue;
                }

                $requireRecompute = false;

                if (isset($metadata->crypt['hash']) &&
                   isset($metadata->crypt['hash'][$field])
                ) {
                    $service = $this->serviceLocator->get($metadata->crypt['hash'][$field]['service']);
                    if (! $service instanceof HashServiceInterface) {
                        throw new \Zoop\Shard\Exception\InvalidArgumentException();
                    }
                    $service->hashField($field, $document, $metadata);
                    $requireRecompute = true;

                } elseif (isset($metadata->crypt['blockCipher']) &&
                   isset($metadata->crypt['blockCipher'][$field])
                ) {
                    $service = $this->serviceLocator->get($metadata->crypt['blockCipher'][$field]['service']);
                    if (! $service instanceof BlockCipherServiceInterface) {
                        throw new \Zoop\Shard\Exception\InvalidArgumentException();
                    }
                    $service->encryptField($field, $document, $metadata);
                    $requireRecompute = true;
                }

                if ($requireRecompute) {
                    $unitOfWork->recomputeSingleDocumentChangeSet($metadata, $document);
                }
            }
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
}
