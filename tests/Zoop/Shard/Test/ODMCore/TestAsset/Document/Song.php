<?php

namespace Zoop\Shard\Test\ODMCore\TestAsset\Document;

use Zoop\Shard\Test\ODMCore\TestAsset\Document\RecordLabel;
use Zoop\Shard\Test\ODMCore\TestAsset\Document\Artist;
//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\EmbeddedDocument */
class Song
{
    /**
     * @ODM\String
     */
    protected $name;

    /**
     * @ODM\EmbedOne(
     *      discriminatorField="type",
     *      discriminatorMap={
     *         "RecordLabel"    = "Zoop\Shard\Test\ODMCore\TestAsset\Document\RecordLabel",
     *         "Artist"         = "Zoop\Shard\Test\ODMCore\TestAsset\Document\Artist"
     *      }
     * )
     */
    protected $license;

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

    public function getLicense()
    {
        return $this->license;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function __construct($name, $license)
    {
        $this->name = $name;
        $this->license = $license;
    }
}
