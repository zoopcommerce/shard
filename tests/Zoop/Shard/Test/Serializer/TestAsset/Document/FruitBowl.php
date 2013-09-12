<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class FruitBowl
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\EmbedMany(
     *     discriminatorMap={
     *         "apple"="Zoop\Shard\Test\Serializer\TestAsset\Document\Apple",
     *         "orange"="Zoop\Shard\Test\Serializer\TestAsset\Document\Orange"
     *     },
     *     discriminatorField="type"
     * )
     */
    protected $embeddedFruit;

    /**
     * @ODM\ReferenceMany(
     *     discriminatorMap={
     *         "apple"="Zoop\Shard\Test\Serializer\TestAsset\Document\Apple",
     *         "orange"="Zoop\Shard\Test\Serializer\TestAsset\Document\Orange"
     *     },
     *     discriminatorField="type",
     *     cascade="all"
     * )
     * @Shard\Serializer\Eager
     */
    protected $referencedFruit;

    public function getId()
    {
        return $this->id;
    }

    public function getEmbeddedFruit() {
        return $this->embeddedFruit;
    }

    public function setEmbeddedFruit($embeddedFruit) {
        $this->embeddedFruit = $embeddedFruit;
    }

    public function getReferencedFruit() {
        return $this->referencedFruit;
    }

    public function setReferencedFruit($referencedFruit) {
        $this->referencedFruit = $referencedFruit;
    }

    public function __construct()
    {
        $this->embeddedFruit = new ArrayCollection();
        $this->referencedFruit = new ArrayCollection();
    }
}
