<?php

namespace Zoop\Shard\Test\Validator\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class User
{
    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\String
     * @Shard\Validator\Required
     */
    protected $username;

    /**
     * @ODM\EmbedOne(targetDocument="Profile")
     */
    protected $profile;

    /**
     * @ODM\EmbedMany(targetDocument="Group")
     */
    protected $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

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

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function addGroup($group)
    {
        $this->groups[] = $group;
    }
}
