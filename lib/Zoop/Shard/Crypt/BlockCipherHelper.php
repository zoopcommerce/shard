<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt;

use Zoop\Shard\Crypt\BlockCipher\BlockCipherServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class BlockCipherHelper implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function encryptDocument($document, $metadata){

        if (isset($metadata->crypt['blockCipher'])) {
            foreach ($metadata->crypt['blockCipher'] as $field => $config){
                $service = $this->serviceLocator->get($config['service']);
                if (!$service instanceof BlockCipherServiceInterface){
                    throw new \Zoop\Shard\Exception\InvalidArgumentException();
                }
                $service->encryptField($field, $document, $metadata);
            }
        }

    }

    public function decryptDocument($document, $metadata){

        if (isset($metadata->crypt['blockCipher'])) {
            foreach ($metadata->crypt['blockCipher'] as $field => $config){
                $service = $this->serviceLocator->get($config['service']);
                if (!$service instanceof BlockCipherServiceInterface){
                    throw new \Zoop\Shard\Exception\InvalidArgumentException();
                }
                $service->decryptField($field, $document, $metadata);
            }
        }
    }
}
