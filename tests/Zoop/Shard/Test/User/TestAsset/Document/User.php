<?php

namespace Zoop\Shard\Test\User\TestAsset\Document;

use Zoop\Shard\Test\TestAsset\RoleAwareUser;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl ({
 *     @Shard\Permission\Basic(roles="*",     allow={"create", "read", "update::*"}, deny="update::roles"),
 *     @Shard\Permission\Basic(roles="admin", allow="update::roles"                                   )
 * })
 */
class User extends RoleAwareUser {

}
