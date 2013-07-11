<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\Serializer\ClassName
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*", allow="create"),
 *     @Shard\Permission\Basic(roles="user", allow="read")
 * })
 */
class SecretIngredient {

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /** @ODM\String */
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

    public function __construct($name){
        $this->name = $name;
    }
}
