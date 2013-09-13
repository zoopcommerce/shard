<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\Common\EventArgs as BaseEventArgs;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zoop\Common\User\UserInterface;

/**
 * Arguments for annotation events
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class GetRolesEventArgs extends BaseEventArgs
{
    protected $metadata;

    protected $document;

    protected $user;

    protected $roles;

    public function __construct(
        ClassMetadata $metadata,
        $document,
        UserInterface $user = null,
        array $roles = []
    ) {
        $this->metadata = $metadata;
        $this->document = $document;
        $this->user = $user;
        $this->roles = $roles;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;
    }
}
