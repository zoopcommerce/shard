<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class CakeEager
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\ReferenceMany(targetDocument="Ingredient", cascade="all")
     * @Shard\Serializer\Eager
     */
    protected $ingredients;

    /**
     * @ODM\ReferenceOne(targetDocument="FlavourEager")
     * @Shard\Serializer\Eager
     */
    protected $flavour;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIngredients()
    {
        return $this->ingredients;
    }

    public function setIngredients(array $ingredients)
    {
        $this->ingredients = $ingredients;
    }

    public function addIngredient(Ingredient $ingredient)
    {
        $this->ingredients[] = $ingredient;
    }

    public function getFlavour()
    {
        return $this->flavour;
    }

    public function setFlavour(FlavourEager $flavour)
    {
        $this->flavour = $flavour;
        $flavour->addCake($this);
    }
}
