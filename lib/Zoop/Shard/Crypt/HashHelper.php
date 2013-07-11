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
class HashHelper implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function hashDocument($document, $metadata){

        if (isset($metadata->crypt['hash'])) {
            foreach ($metadata->crypt['hash'] as $field => $config){
                $service = $this->serviceLocator->get($config['service']);
                if (!$service instanceof HashServiceInterface){
                    throw new \Zoop\Shard\Exception\InvalidArgumentException();
                }
                $service->hashField($field, $document, $metadata);
            }
        }
    }
}
