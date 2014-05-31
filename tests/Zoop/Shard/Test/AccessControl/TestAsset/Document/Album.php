<?php

namespace Zoop\Shard\Test\AccessControl\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*",allow="*")
 * })
 */
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
     * @ODM\EmbedMany(targetDocument="Artist")
     */
    protected $artists = array();

    /**
     * @ODM\EmbedMany(
     *   discriminatorMap={
     *     "Artist"="Artist",
     *     "SongWriter"="SongWriter"
     *   }
     * )
     */
    protected $songWriters = array();

    /**
     * @ODM\EmbedMany(
     *   discriminatorMap={
     *     "Licensing"="Licensing",
     *     "Mechanical"="Mechanical"
     *   }
     * )
     */
    protected $royalties = array();

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
     * @return array
     */
    public function getArtists()
    {
        return $this->artists;
    }

    /**
     * @return array
     */
    public function getSongWriters()
    {
        return $this->songWriters;
    }

    /**
     * @param array $artists
     */
    public function setArtists(array $artists)
    {
        $this->artists = $artists;
    }

    /**
     * @param array $songWriters
     */
    public function setSongWriters(array $songWriters)
    {
        $this->songWriters = $songWriters;
    }

    /**
     * @param SongWriter|Artist $songWriter
     */
    public function addSongWriter($songWriter)
    {
        $this->songWriters[] = $songWriter;
    }

    /**
     * @param Artist $artist
     */
    public function addArtist(Artist $artist)
    {
        $this->artists[] = $artist;
    }

    /**
     * @return array
     */
    public function getRoyalties()
    {
        return $this->royalties;
    }

    /**
     * @param array $royalties
     */
    public function setRoyalties(array $royalties)
    {
        $this->royalties = $royalties;
    }

    /**
     * @param Mechanical|Licensing $royalty
     */
    public function addRoyalty($royalty)
    {
        $this->royalties[] = $royalty;
    }

    public function __construct($name)
    {
        $this->name = $name;
    }
}
