<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as DoctrineClassMetadata;
use Zoop\Shard\Core\ClassMetadataTrait;

/**
 * Extends ClassMetadata to support Shard metadata
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ClassMetadata extends DoctrineClassMetadata
{
    use ClassMetadataTrait;
}
