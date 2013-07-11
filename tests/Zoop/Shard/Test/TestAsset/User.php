<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Test\TestAsset;

use Zoop\Common\User\UserInterface;
use Zoop\Shard\User\DataModel\UserTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

class User implements UserInterface {

    use UserTrait;
}
