<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt\Hash;

use Zend\Crypt\Password\Bcrypt;
use Zoop\Common\Crypt\SaltInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class BasicHashService implements HashServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function hashValue($plaintext, $salt = null){
        $bcrypt = new Bcrypt(['salt' => $salt]);
        return $bcrypt->create($plaintext);

    }

    public function hashField($field, $document, $metadata){

        if (isset($metadata->crypt['hash'][$field]['salt'])){
            $saltInterface = $this->serviceLocator->get($metadata->crypt['hash'][$field]['salt']);
        } else {
            $saltInterface = $document;
        }
        if ($saltInterface instanceof SaltInterface){
            $salt = $saltInterface->getSalt();
        }

        $metadata->reflFields[$field]->setValue(
            $document,
            $this->hashValue(
                $metadata->reflFields[$field]->getValue($document),
                $salt
            )
        );
    }
}
