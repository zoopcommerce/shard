<?php

namespace Zoop\Shard\User\DataModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

trait UserTrait {

    /**
     * @ODM\Id(strategy="none")
     * @ODM\Index(unique = true, order = "asc")
     * @Shard\Validator\Chain({
     *     @Shard\Validator\Required,
     *     @Shard\Validator\Slug
     * })
     */
    protected $username;

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = (string) $username;
    }
}
