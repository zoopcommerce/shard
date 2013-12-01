<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document(collection="Group")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorMap({
 *     "Administrators" = "Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl\Administrators",
 *     "Guests"         = "Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl\Guests"
 * })
 */
abstract class Group
{

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\EmbedOne(
     *     discriminatorMap={
     *         "Administrator"  = "Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl\Administrator"
     *     }
     * )
     */
    protected $owner;

    public function getId()
    {
        return $this->id;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(Administrator $owner)
    {
        $this->owner = $owner;
    }

}
