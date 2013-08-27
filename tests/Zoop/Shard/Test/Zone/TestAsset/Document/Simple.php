<?php

namespace Zoop\Shard\Test\Zone\TestAsset\Document;

use Zoop\Shard\Zone\ZoneInterface;
use Zoop\Shard\Zone\DataModel\ZoneTrait;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class Simple implements ZoneInterface
{

    use ZoneTrait;

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\String
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
