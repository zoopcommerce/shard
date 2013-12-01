<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

abstract class AbstractUser
{

    /**
     * @ODM\String
     */
    protected $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

}
