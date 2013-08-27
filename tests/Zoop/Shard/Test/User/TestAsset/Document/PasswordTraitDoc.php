<?php

namespace Zoop\Shard\Test\User\TestAsset\Document;

use Zoop\Common\Crypt\Salt;
use Zoop\Common\User\PasswordInterface;
use Zoop\Shard\User\DataModel\PasswordTrait;

//Annotaion imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document
 * @Shard\AccessControl ({
 *     @Shard\Permission\Basic(roles="*",     allow={"create", "read", "update"}, deny="update::password"),
 *     @Shard\Permission\Basic(roles="admin", allow="update::password"                                   )
 * })
 */
class PasswordTraitDoc implements PasswordInterface
{
    use PasswordTrait;

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}
