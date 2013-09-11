<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt;

use Doctrine\Common\EventSubscriber;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Shard\Crypt\BlockCipher\BlockCipherServiceInterface;
use Zoop\Shard\Crypt\Hash\HashServiceInterface;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\AbstractChangeEventArgs;
use Zoop\Shard\Core\MetadataSleepEventArgs;

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
            CoreEvents::CRYPT,
            CoreEvents::METADATA_SLEEP,
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

    public function crypt(AbstractChangeEventArgs $eventArgs)
    {
        $this->hashFields($eventArgs);
        $this->blockCipherFields($eventArgs);
    }

    protected function hashFields(AbstractChangeEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();

        if (! isset($metadata->crypt['hash'])){
            return;
        }

        $document = $eventArgs->getDocument();
        $changeSet = $eventArgs->getChangeSet();

        foreach ($metadata->crypt['hash'] as $field => $setting){
            if ($this->hasChanged($field, $changeSet)) {
                $service = $this->serviceLocator->get($setting['service']);
                if (! $service instanceof HashServiceInterface) {
                    throw new \Zoop\Shard\Exception\InvalidArgumentException();
                }
                $service->hashField($field, $document, $metadata);
                $eventArgs->addRecompute($field);
            }
        }
    }

    protected function blockCipherFields(AbstractChangeEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getMetadata();

        if (! isset($metadata->crypt['blockCipher'])){
            return;
        }

        $document = $eventArgs->getDocument();
        $changeSet = $eventArgs->getChangeSet();

        foreach ($metadata->crypt['blockCipher'] as $field => $setting){
            if ($this->hasChanged($field, $changeSet)) {
                $service = $this->serviceLocator->get($setting['service']);
                if (! $service instanceof BlockCipherServiceInterface) {
                    throw new \Zoop\Shard\Exception\InvalidArgumentException();
                }
                $service->encryptField($field, $document, $metadata);
                $eventArgs->addRecompute($field);
            }
        }
    }

    protected function hasChanged($field, $changeSet){

        if (!isset($changeSet[$field])) {
            return false;
        }

        list($old, $new) = $changeSet[$field];

        // Check for change
        if ($old == $new || (! isset($new) || $new == '')) {
            return false;
        }

        return true;
    }

    public function metadataSleep(MetadataSleepEventArgs $eventArgs){
        $eventArgs->addSerialized('crypt');
    }
}
