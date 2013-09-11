<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\User\DataModel;

use Zoop\Shard\Crypt\SaltGenerator;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * Implementation of Zoop\Common\User\PasswordInterface
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait PasswordTrait
{
    /**
     * @ODM\String
     * @Shard\Serializer\Ignore
     * @Shard\Validator\Chain({
     *     @Shard\Validator\Required,
     *     @Shard\Validator\Password
     * })
     * @Shard\Crypt\Hash
     */
    protected $password;

    /**
     * @ODM\String
     * @Shard\Validator\Required
     */
    protected $salt;

    /**
     * Returns encrypted password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @param string $plaintext
     */
    public function setPassword($plaintext)
    {
        $this->password = (string) $plaintext;
    }

    public function getSalt()
    {
        if (!isset($this->salt)) {
            $this->salt = SaltGenerator::generateSalt();
        }
        return $this->salt;
    }

    public function setSalt($value)
    {
        return $this->salt = (string) $value;
    }
}
