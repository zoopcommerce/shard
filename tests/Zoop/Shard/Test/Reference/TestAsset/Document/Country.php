<?php

namespace Zoop\Shard\Test\Reference\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class Country {

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;
}
