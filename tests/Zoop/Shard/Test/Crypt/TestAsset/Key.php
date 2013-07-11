<?php

namespace Zoop\Shard\Test\Crypt\TestAsset;

use Zoop\Common\Crypt\KeyInterface;

class Key implements KeyInterface {

    public function getKey() {

        return 'test key phrase';
    }
}