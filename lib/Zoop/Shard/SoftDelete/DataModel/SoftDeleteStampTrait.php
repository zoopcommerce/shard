<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\SoftDelete\DataModel;

use Zoop\Shard\SoftDelete\DataModel\SoftDeletedOnTrait;
use Zoop\Shard\SoftDelete\DataModel\SoftDeletedByTrait;
use Zoop\Shard\SoftDelete\DataModel\RestoredOnTrait;
use Zoop\Shard\SoftDelete\DataModel\RestoredByTrait;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait SoftDeleteStampTrait {
   use SoftDeletedOnTrait;
   use SoftDeletedByTrait;
   use RestoredByTrait;
   use RestoredOnTrait;
}