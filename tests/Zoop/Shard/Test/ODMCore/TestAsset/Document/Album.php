<?php

namespace Zoop\Shard\Test\ODMCore\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;
//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class Album
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\String
     */
    protected $name;

    /**
     * @ODM\EmbedMany(targetDocument="\Zoop\Shard\Test\ODMCore\TestAsset\Document\Song")
     */
    protected $songs;

    public function __construct($name)
    {
        $this->songs = new ArrayCollection;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection
     */
    public function getSongs()
    {
        return $this->songs;
    }

    /**
     * @param ArrayCollection $songs
     */
    public function setSongs($songs)
    {
        $this->songs = $songs;
    }

    /**
     * @param Song $song
     */
    public function addSong(Song $song)
    {
        $this->getSongs()->add($song);
    }
}
