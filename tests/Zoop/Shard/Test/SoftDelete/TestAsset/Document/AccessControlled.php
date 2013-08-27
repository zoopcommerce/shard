<?php

namespace Zoop\Shard\Test\SoftDelete\TestAsset\Document;

use Zoop\Shard\SoftDelete\DataModel\SoftDeleteableTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*",               allow={"create", "read"}),
 *     @Shard\Permission\Basic(roles={"user", "admin"}, allow="softDelete"      ),
 *     @Shard\Permission\Basic(roles="admin",           allow="restore"         )
 * })
 */
class AccessControlled
{
    use SoftDeleteableTrait;

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
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
