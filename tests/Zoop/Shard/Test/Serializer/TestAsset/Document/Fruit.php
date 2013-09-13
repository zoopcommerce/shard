<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField(fieldName="type")
 * @ODM\DiscriminatorMap({
 *     "apple"="Zoop\Shard\Test\Serializer\TestAsset\Document\Apple",
 *     "orange"="Zoop\Shard\Test\Serializer\TestAsset\Document\Orange"
 * })
 */
abstract class Fruit
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     *
     * @ODM\String
     */
    protected $variety;

    public function getId()
    {
        return $this->id;
    }

    public function getVariety()
    {
        return $this->variety;
    }

    public function setVariety($variety)
    {
        $this->variety = $variety;
    }
}
