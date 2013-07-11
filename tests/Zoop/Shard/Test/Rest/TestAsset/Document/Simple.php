<?php

namespace Zoop\Shard\Test\Rest\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class Simple {

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    public function getId() {
        return $this->id;
    }
}
