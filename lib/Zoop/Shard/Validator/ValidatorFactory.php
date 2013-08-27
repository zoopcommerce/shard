<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Validator;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ValidatorFactory
{
    public static function create($def)
    {
        $class = $def['class'];
        if (isset($def['options'])) {
            $options = $def['options'];
        } else {
            $options = [];
        }

        if ($class == 'Zoop\Mystique\Chain') {
            $validators = [];
            foreach ($options['validators'] as $subDef) {
                $validators[] = self::create($subDef);
            }
            $options['validators'] = $validators;
        }

        return new $class($options);
    }
}
