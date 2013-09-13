<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class FlavourEager
{

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /** @ODM\String */
    protected $name;

    /**
     * @ODM\ReferenceMany(targetDocument="CakeEager")
     * @Shard\Serializer\Eager
     */
    protected $cakes;

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

    public function getCakes()
    {
        return $this->cakes;
    }

    public function setCakes(array $cakes)
    {
        $this->cakes = $cakes;
    }

    public function addCake(CakeEager $cake)
    {
        $this->cakes[] = $cake;
    }

    public function __construct($name)
    {
        $this->name = $name;
    }
}
