<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze\DataModel;

use Zoop\Shard\Freeze\DataModel\FrozenOnTrait;
use Zoop\Shard\Freeze\DataModel\FrozenByTrait;
use Zoop\Shard\Freeze\DataModel\ThawedOnTrait;
use Zoop\Shard\Freeze\DataModel\ThawedByTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait FreezeStampTrait
{
    use FrozenOnTrait;
    use FrozenByTrait;
    use ThawedByTrait;
    use ThawedOnTrait;

}
