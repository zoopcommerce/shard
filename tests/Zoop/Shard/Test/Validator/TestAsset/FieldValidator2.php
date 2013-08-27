<?php

namespace Zoop\Shard\Test\Validator\TestAsset;

use Zoop\Mystique\ValidatorInterface;
use Zoop\Mystique\Result;

class FieldValidator2 implements ValidatorInterface
{
    public function isValid($value)
    {
        $messages = [];

        if ($value == 'valid' || $value == 'alsoValid') {
            $result = true;
        } else {
            $messages[] = 'invalid name 2';
            $result = false;
        }

        return new Result(['value' => $result, 'messages' => $messages]);
    }
}
