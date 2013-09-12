<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class Family
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\EmbedMany(
     *     strategy="set",
     *     discriminatorMap={
     *         "male"="Zoop\Shard\Test\Serializer\TestAsset\Document\Male",
     *         "female"="Zoop\Shard\Test\Serializer\TestAsset\Document\Female"
     *     },
     *     discriminatorField="gender"
     * )
     */
    protected $parents;

    /**
     * @ODM\ReferenceMany(
     *     strategy="set",
     *     discriminatorMap={
     *         "male"="Zoop\Shard\Test\Serializer\TestAsset\Document\Male",
     *         "female"="Zoop\Shard\Test\Serializer\TestAsset\Document\Female"
     *     },
     *     discriminatorField="gender",
     *     cascade="all"
     * )
     * @Shard\Serializer\Eager
     */
    protected $kids;

    public function getId()
    {
        return $this->id;
    }

    public function getParents() {
        return $this->parents;
    }

    public function setParents($parents) {
        $this->parents = $parents;
    }

    public function getKids() {
        return $this->kids;
    }

    public function setKids($kids) {
        $this->kids = $kids;
    }

    public function __construct()
    {
        $this->parents = new ArrayCollection();
        $this->kids = new ArrayCollection();
    }
}
