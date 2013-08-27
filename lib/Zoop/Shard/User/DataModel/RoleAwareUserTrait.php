<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\User\DataModel;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait RoleAwareUserTrait
{
    /**
     * @ODM\Collection
     */
    protected $roles = [];

    /**
     *
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     *
     * @param string $role
     */
    public function addRole($role)
    {
        $this->roles[] = (string) $role;
        return $this;
    }

    /**
     *
     * @param string $role
     */
    public function removeRole($role)
    {
        if (($key = array_search((string)$role, $this->roles)) !== false) {
            unset($this->roles[$key]);
        }
    }

    /**
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     *
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array((string)$role, $this->roles);
    }
}
