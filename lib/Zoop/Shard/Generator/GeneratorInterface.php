<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Generator;

/**
 * Generate file from mapping information.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
interface GeneratorInterface
{
    public function generate($name, $class, $options = null);
}
