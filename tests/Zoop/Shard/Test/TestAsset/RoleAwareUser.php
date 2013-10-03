<?php

namespace Zoop\Shard\Test\TestAsset;

use Zoop\Common\User\RoleAwareUserInterface;
use Zoop\Shard\User\DataModel\RoleAwareUserTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class RoleAwareUser extends User implements RoleAwareUserInterface
{
    use RoleAwareUserTrait;
}
