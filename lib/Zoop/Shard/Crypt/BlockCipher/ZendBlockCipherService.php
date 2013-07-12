<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt\BlockCipher;

use Zend\Crypt\BlockCipher;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Common\Crypt\SaltInterface;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ZendBlockCipherService implements BlockCipherServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function encryptValue($plaintext, $key, $salt = null){
        if ( ! isset($plaintext) || $plaintext == ''){
            return $plaintext;
        }

        $cipher = BlockCipher::factory('mcrypt', array('algorithm' => 'aes'));
        if (isset($salt)) {
            $cipher->setSalt(substr($salt, 0, $cipher->getCipher()->getSaltSize()));
        }
        $cipher->setKey($key);
        return $cipher->encrypt($plaintext);
    }

    public function decryptValue($encryptedText, $key){
        $cipher = BlockCipher::factory('mcrypt', array('algorithm' => 'aes'));
        $cipher->setKey($key);
        return $cipher->decrypt($encryptedText);
    }

    public function encryptField($field, $document, $metadata){

        if (isset($metadata->crypt['blockCipher'][$field]['salt'])){
            $saltInterface = $this->serviceLocator->get($metadata->crypt['blockCipher'][$field]['salt']);
        } else {
            $saltInterface = $document;
        }
        if ($saltInterface instanceof SaltInterface){
            $salt = $saltInterface->getSalt();
        } else {
            $salt = null;
        }

        $key = $this->serviceLocator->get($metadata->crypt['blockCipher'][$field]['key'])->getKey();

        $metadata->reflFields[$field]->setValue(
            $document,
            $this->encryptValue(
                $metadata->reflFields[$field]->getValue($document),
                $key,
                $salt
            )
        );
    }

    public function decryptField($field, $document, $metadata){

        $key = $this->serviceLocator->get($metadata->crypt['blockCipher'][$field]['key'])->getKey();

        $metadata->reflFields[$field]->setValue(
            $document,
            $this->decryptValue(
                $metadata->reflFields[$field]->getValue($document),
                $key
            )
        );
    }
}
