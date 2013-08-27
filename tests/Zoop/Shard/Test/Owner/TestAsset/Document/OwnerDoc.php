<?php

namespace Zoop\Shard\Test\Owner\TestAsset\Document;

use Zoop\Shard\Owner\DataModel\OwnerTrait;

//Annotaion imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*",     allow={"create", "read"}                     ),
 *     @Shard\Permission\Basic(roles="owner", allow="update::*",       deny="update::owner"),
 *     @Shard\Permission\Basic(roles="admin", allow="update::owner"                        )
 * })
 */
class OwnerDoc
{
    use OwnerTrait;

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     *
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
