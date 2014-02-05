<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;
//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class Post
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\String
     */
    protected $title;

    /**
     * @ODM\Collection
     * @Shard\Unserializer\Collection(type="array")
     */
    protected $arrayTags = [];

    /**
     * @ODM\Collection
     * @Shard\Unserializer\Collection(type="ArrayCollection")
     */
    protected $arrayCollectionTags;

    public function __construct()
    {
        $this->arrayCollectionTags = new ArrayCollection;
    }

    /**
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * 
     * @return array
     */
    public function getArrayTags()
    {
        return $this->arrayTags;
    }

    /**
     * 
     * @return ArrayCollection
     */
    public function getArrayCollectionTags()
    {
        return $this->arrayCollectionTags;
    }

    /**
     * 
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * 
     * @param array $arrayTags
     */
    public function setArrayTags($arrayTags = [])
    {
        $this->arrayTags = $arrayTags;
    }

    /**
     * 
     * @param ArrayCollection $arrayCollectionTags
     */
    public function setArrayCollectionTags(ArrayCollection $arrayCollectionTags)
    {
        $this->arrayCollectionTags = $arrayCollectionTags;
    }

    /**
     * 
     * @param string $arrayTag
     */
    public function addArrayTag($arrayTag)
    {
        $this->arrayTags[] = $arrayTag;
    }

    /**
     * 
     * @param string $arrayCollectionTag
     */
    public function addArrayCollectionTag($arrayCollectionTag)
    {
        $this->arrayCollectionTags->add($arrayCollectionTag);
    }
}
