<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ChangeSet
{
    protected $changeSet;

    public function setField($field, $oldValue, $newValue) {
        $this->changeSet[$field] = [$oldValue, $newValue];
    }

    public function removeField($field) {
        unset($this->changeSet[$field]);
    }

    public function getField($field) {
        if (isset($this->changeSet[$field])) {
            return $this->changeSet[$field];
        }
    }

    public function hasField($field)
    {
        return array_key_exists($field, $this->changeSet);
    }

    public function getFieldNames()
    {
        return array_keys($this->changeSet);
    }

    public function __construct(array $changeSet = []) {
        $this->changeSet = $changeSet;
    }
}
