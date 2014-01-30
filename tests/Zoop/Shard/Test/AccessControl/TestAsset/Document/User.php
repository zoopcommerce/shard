<?php

namespace Zoop\Shard\Test\AccessControl\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*",              allow="read"  ),
 *     @Shard\Permission\Basic(roles="partialCreator", allow="create"),
 *     @Shard\Permission\Basic(roles="creator",        allow="create"),
 * })
 */
class User
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\String
     */
    protected $username;


    /** @ODM\EmbedOne(targetDocument="Profile") */
    protected $profile;

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;
    }
}
