<?php

namespace Zoop\Shard\Test\Serializer\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/** @ODM\Document */
class User {

    /**
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     */
    protected $username;

    /**
     * @ODM\Field(type="string")
     * @Shard\Serializer\Ignore
     */
    protected $password;


    /** @ODM\EmbedMany(targetDocument="Group") */
    protected $groups;

    /** @ODM\EmbedOne(targetDocument="Profile") */
    protected $profile;

    /**
     * @ODM\Field(type="string")
     */
    protected $location;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function location() {
        return $this->location;
    }

    public function defineLocation($location) {
        $this->location = $location;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function setGroups(array $groups){
        $this->groups = $groups;
    }

    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
    }

    public function getProfile() {
        return $this->profile;
    }

    public function setProfile(Profile $profile) {
        $this->profile = $profile;
    }
}
