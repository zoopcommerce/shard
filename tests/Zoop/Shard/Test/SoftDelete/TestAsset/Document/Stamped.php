<?php

namespace Zoop\Shard\Test\SoftDelete\TestAsset\Document;

use Zoop\Shard\SoftDelete\DataModel\SoftDeleteStampTrait;
use Zoop\Shard\SoftDelete\DataModel\SoftDeleteableTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class Stamped {

    use SoftDeleteableTrait;
    use SoftDeleteStampTrait;

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\String
     */
    protected $name;

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
}
