<?php

namespace Zoop\Shard\Test\Freeze\TestAsset\Document;

use Zoop\Shard\Freeze\DataModel\FreezeableTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*",               allow={"create", "read"}),
 *     @Shard\Permission\Basic(roles={"user", "admin"}, allow="freeze"          ),
 *     @Shard\Permission\Basic(roles="admin",           allow="thaw"            )
 * })
 */
class AccessControlled
{
    use FreezeableTrait;

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
