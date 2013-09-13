<?php

namespace Zoop\Shard\Test\Validator\TestAsset;

use Zoop\Mystique\Base;
use Zoop\Mystique\Result;

class ClassValidator extends Base
{
    public function isValid($value)
    {
        return new Result(['value' => true]);
    }
}
