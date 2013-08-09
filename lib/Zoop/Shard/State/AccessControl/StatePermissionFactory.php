<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State\AccessControl;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zoop\Shard\AccessControl\PermissionFactoryInterface;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class StatePermissionFactory implements PermissionFactoryInterface
{
    public static function get(ClassMetadata $metadata, array $options){

        return new StatePermission(
            $options['roles'],
            $options['allow'],
            $options['deny'],
            $options['states'],
            $metadata->state
        );
    }
}

