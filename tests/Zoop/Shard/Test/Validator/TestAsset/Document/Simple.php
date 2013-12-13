<?php

namespace Zoop\Shard\Test\Validator\TestAsset\Document;

//Annotaion imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\Validator(class = "Zoop\Shard\Test\Validator\TestAsset\ClassValidator")
 */
class Simple
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\String
     * @Shard\Validator\Chain({
     *     @Shard\Validator\Required,
     *     @Shard\Validator(class = "Zoop\Shard\Test\Validator\TestAsset\FieldValidator1"),
     *     @Shard\Validator(class = "Zoop\Shard\Test\Validator\TestAsset\FieldValidator2")
     * })
     */
    protected $name;

    public function getId()
    {
        return $this->id;
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
