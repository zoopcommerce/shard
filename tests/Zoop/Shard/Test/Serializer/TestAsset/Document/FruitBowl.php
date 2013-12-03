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
     *     }
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

    /**
     * @ODM\EmbedOne(
     *     discriminatorMap={
     *         "apple"="Zoop\Shard\Test\Serializer\TestAsset\Document\EmbeddedApple",
     *         "orange"="Zoop\Shard\Test\Serializer\TestAsset\Document\EmbeddedOrange"
     *     }
     * )
     * @Shard\Serializer\Eager
     */
    protected $embeddedSingleFruit;

    /**
     * @ODM\EmbedOne(
     *     targetDocument="Zoop\Shard\Test\Serializer\TestAsset\Document\EmbeddedApple"
     * )
     * @Shard\Serializer\Eager
     */
    protected $embeddedSingleFruitTarget;

    /**
     * @ODM\ReferenceOne(
     *     discriminatorMap={
     *         "apple"="Zoop\Shard\Test\Serializer\TestAsset\Document\Apple",
     *         "orange"="Zoop\Shard\Test\Serializer\TestAsset\Document\Orange"
     *     },
     *     discriminatorField="type",
     *     cascade="all"
     * )
     * @Shard\Serializer\Eager
     */
    protected $referencedSingleFruit;

    public function getId()
    {
        return $this->id;
    }

    public function getEmbeddedFruit()
    {
        return $this->embeddedFruit;
    }

    public function setEmbeddedFruit($embeddedFruit)
    {
        $this->embeddedFruit = $embeddedFruit;
    }

    public function getReferencedFruit()
    {
        return $this->referencedFruit;
    }

    public function setReferencedFruit($referencedFruit)
    {
        $this->referencedFruit = $referencedFruit;
    }

    public function getEmbeddedSingleFruit()
    {
        return $this->embeddedSingleFruit;
    }

    public function setEmbeddedSingleFruit($embeddedSingleFruit)
    {
        $this->embeddedSingleFruit = $embeddedSingleFruit;
    }

    public function getReferencedSingleFruit()
    {
        return $this->referencedSingleFruit;
    }

    public function setReferencedSingleFruit($referencedSingleFruit)
    {
        $this->referencedSingleFruit = $referencedSingleFruit;
    }

    public function getEmbeddedSingleFruitTarget()
    {
        return $this->embeddedSingleFruitTarget;
    }

    public function setEmbeddedSingleFruitTarget(EmbeddedApple $embeddedSingleFruitTarget)
    {
        $this->embeddedSingleFruitTarget = $embeddedSingleFruitTarget;
    }

    public function __construct()
    {
        $this->embeddedFruit = new ArrayCollection();
        $this->referencedFruit = new ArrayCollection();
    }

}
