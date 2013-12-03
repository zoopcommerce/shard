<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class Administrators extends AbstractGroup
{

    /**
     * @ODM\EmbedMany(
     *     discriminatorMap={
     *         "Administrator"  = "Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl\Administrator",
     *         "SuperUser"      = "Zoop\Shard\Test\Serializer\TestAsset\Document\AccessControl\SuperUser"
     *     }
     * )
     */
    protected $users;

    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers($users)
    {
        $this->users = $users;
    }

    public function addUser(UserInterface $user)
    {
        $this->users[] = $user;
    }

}
