<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class BasicPermissionFactory implements PermissionFactoryInterface
{

    public static function get(ClassMetadata $metadata, array $options){
        return new BasicPermission($options['roles'], $options['allow'], $options['deny']);
    }
}

