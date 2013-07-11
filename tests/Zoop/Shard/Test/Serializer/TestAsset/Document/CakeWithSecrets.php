<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\Serializer\ClassName
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*", allow="*")
 * })
 */
class CakeWithSecrets {

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\ReferenceMany(targetDocument="Ingredient", simple=true, cascade="all")
     * @Shard\Serializer\Eager
     */
    protected $ingredients;

    /**
     * @ODM\ReferenceMany(targetDocument="SecretIngredient", simple=true, cascade="all")
     * @Shard\Serializer\Eager
     */
    protected $secretIngredients;


    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
        $this->secretIngredients = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getIngredients()
    {
        return $this->ingredients;
    }

    public function setIngredients(array $ingredients){
        $this->ingredients = $ingredients;
    }

    public function addIngredient(Ingredient $ingredient)
    {
        $this->ingredients[] = $ingredient;
    }

    public function getSecretIngredients()
    {
        return $this->secretIngredients;
    }

    public function setSecretIngredients(array $secretIngredients){
        $this->secretIngredients = $secretIngredients;
    }

    public function addSecretIngredient(SecretIngredient $secretIngredient)
    {
        $this->secretIngredients[] = $secretIngredient;
    }
}
