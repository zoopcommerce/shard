<?php

namespace Zoop\Shard\Test\Crypt\TestAsset\Document;

//Annotaion imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class CryptValidatorDoc
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\String
     * @Shard\Crypt\BlockCipher(
     *     key = "testkey"
     * )
     * @Shard\Validator\Email
     */
    protected $email;

    /**
     *
     * @ODM\String
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
