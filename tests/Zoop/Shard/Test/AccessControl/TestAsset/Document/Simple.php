<?php

namespace Zoop\Shard\Test\AccessControl\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*",          allow="read"                  ),
 *     @Shard\Permission\Basic(roles="creator",    allow="create",  deny="read"  ),
 *     @Shard\Permission\Basic(roles="reader",     allow="read"                  ),
 *     @Shard\Permission\Basic(roles="updater",    allow="update::*"             ),
 *     @Shard\Permission\Basic(roles="deletor",    allow="delete"                ),
 *     @Shard\Permission\Basic(roles="admin",      allow="*",       deny="delete"),
 *     @Shard\Permission\Basic(roles="superadmin", allow="*"                     )
 * })
 */
class Simple {

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
        $this->name = (string) $name;
    }
}
