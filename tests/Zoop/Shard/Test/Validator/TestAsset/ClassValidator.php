<?php

namespace Zoop\Shard\Test\Validator\TestAsset;

use Zoop\Mystique\ValidatorInterface;
use Zoop\Mystique\Result;

class ClassValidator implements ValidatorInterface {

    public function isValid($value) {
        return new Result(['value' => true]);
    }
}