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
class BlockCipherHelper implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function encryptDocument($document, $metadata)
    {
        $cryptMetadata = $metadata->getCrypt();
        if (isset($cryptMetadata['blockCipher'])) {
            foreach ($cryptMetadata['blockCipher'] as $field => $config) {
                $this->getBlockCipherService($config)->encryptField($field, $document, $metadata);
            }
        }
    }

    public function decryptDocument($document, $metadata)
    {
        $cryptMetadata = $metadata->getCrypt();
        if (isset($cryptMetadata['blockCipher'])) {
            foreach ($cryptMetadata['blockCipher'] as $field => $config) {
                $this->getBlockCipherService($config)->decryptField($field, $document, $metadata);
            }
        }
    }

    public function encryptField($field, $document, $metadata)
    {
        $cryptMetadata = $metadata->getCrypt();
        if (isset($cryptMetadata['blockCipher']) && isset($cryptMetadata['blockCipher'][$field])) {
            $this->getBlockCipherService($cryptMetadata['blockCipher'][$field])
                ->encryptField($field, $document, $metadata);
        }
    }

    public function decryptField($field, $document, $metadata)
    {
        $cryptMetadata = $metadata->getCrypt();
        if (isset($cryptMetadata['blockCipher']) && isset($cryptMetadata['blockCipher'][$field])) {
            $this->getBlockCipherService($cryptMetadata['blockCipher'][$field])
                ->decryptField($field, $document, $metadata);
        }
    }

    protected function getBlockCipherService($config)
    {
        $service = $this->serviceLocator->get($config['service']);
        if (! $service instanceof BlockCipherServiceInterface) {
            throw new \Zoop\Shard\Exception\InvalidArgumentException();
        }
        return $service;
    }
}
