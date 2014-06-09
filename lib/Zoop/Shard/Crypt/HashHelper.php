<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt;

use Zoop\Shard\Crypt\Hash\HashServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class HashHelper implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function hashDocument($document, $metadata)
    {
        $cryptMetadata = $metadata->getCrypt();
        if (isset($cryptMetadata['hash'])) {
            foreach ($cryptMetadata['hash'] as $field => $config) {
                $this->getHashService($config)->hashField($field, $document, $metadata);
            }
        }
    }

    public function hashField($field, $document, $metadata)
    {
        $cryptMetadata = $metadata->getCrypt();
        if (isset($cryptMetadata['hash']) && isset($cryptMetadata['hash'][$field])) {
            $this->getHashService($cryptMetadata['hash'][$field])->hashField($field, $document, $metadata);
        }
    }

    protected function getHashService($config)
    {
        $service = $this->serviceLocator->get($config['service']);
        if (! $service instanceof HashServiceInterface) {
            throw new \Zoop\Shard\Exception\InvalidArgumentException();
        }
        return $service;
    }
}
