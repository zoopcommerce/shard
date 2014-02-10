<?php

namespace Zoop\Shard\Test\Crypt\TestAsset;

use Zoop\Common\Crypt\SaltInterface;

class Salt implements SaltInterface
{
    public function getSalt()
    {
        return 'test salt phrase';
    }
}
