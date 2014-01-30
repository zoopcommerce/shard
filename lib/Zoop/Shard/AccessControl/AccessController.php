<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Common\User\RoleAwareUserInterface;
use Zoop\Shard\Core\ChangeSet;
use Zoop\Shard\Core\EventManagerTrait;

/**
 * Defines methods for a manager object to check permssions
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AccessController implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use EventManagerTrait;

    protected $permissions = [];

    /**
     * Determines if an action can be done by the current User
     *
     * @param  array                                              $action
     * @param  \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     * @param  type                                               $document
     * @return \Zoop\Shard\AccessControl\IsAllowedResult
     */
    public function areAllowed(array $actions, ClassMetadata $metadata, $document = null, ChangeSet $changeSet = null)
    {
        $result = new AllowedResult(false);

        if (!$metadata->hasProperty('permissions')) {
            return $result;
        }

        $roles = $this->getRoles($metadata, $document);

        foreach ($this->getPermissions($metadata) as $permission) {

            $newResult = $permission->areAllowed($roles, $actions);
            $allowed = $newResult->getAllowed();
            if (! isset($allowed)) {
                continue;
            }

            $result->setAllowed($allowed);

            $result->setNew(array_merge($result->getNew(), $newResult->getNew()));
            $result->setOld(array_merge($result->getOld(), $newResult->getOld()));
        }

        if (isset($document)) {
            return $this->testCritieraAgainstDocument($metadata, $document, $changeSet, $result);
        }

        return $result;
    }

    protected function testCritieraAgainstDocument($metadata, $document, $changeSet, $result)
    {
        if (count($result->getNew()) > 0) {
            foreach ($result->getNew() as $field => $value) {
                $newValue = $metadata->getFieldValue($document, $field);
                if (! $this->testCriteriaAgainstValue($newValue, $value)) {
                    $result->setAllowed(false);

                    return $result;
                }
            }
        }

        if (count($result->getOld()) > 0) {
            foreach ($result->getOld() as $field => $value) {
                $oldValue = $changeSet->getField($field)[0];
                if (! $this->testCriteriaAgainstValue($oldValue, $value)) {
                    $result->setAllowed(false);

                    return $result;
                }
            }
        }

        return $result;
    }

    protected function testCriteriaAgainstValue($documentValue, $testValue)
    {
        if ((isset($testValue['$regex']) && !preg_match($testValue['$regex'], $documentValue)) ||
            (is_string($testValue) && $documentValue != $testValue)
        ) {
            return false;
        }

        if (is_array($testValue) && array_key_exists('$or', $testValue)) {
            foreach ($testValue['$or'] as $option) {
                if ($this->testCriteriaAgainstValue($documentValue, $option)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    protected function getPermissions($metadata)
    {
        $class = $metadata->name;

        if (! isset($this->permissions[$class])) {
            $this->permissions[$class] = [];
        }

        foreach ($metadata->getPermissions() as $index => $permissionMetadata) {
            if (! isset($this->permissions[$class][$index])) {
                $factory = $permissionMetadata['factory'];
                $this->permissions[$class][$index] = $factory::get($metadata, $permissionMetadata['options']);
            }
        }

        return $this->permissions[$class];
    }

    protected function getRoles($metadata, $document)
    {
        $eventManager = $this->getEventManager();

        $user = $this->getUser();
        $event = new GetRolesEventArgs($metadata, $document, $user);
        $eventManager->dispatchEvent(Events::GET_ROLES, $event);

        if ($user instanceof RoleAwareUserInterface) {
            return array_unique(array_merge($user->getRoles(), $event->getRoles()));
        } else {
            return $event->getRoles();
        }
    }

    protected function getUser()
    {
        if ($this->serviceLocator->has('user') &&
            $user = $this->serviceLocator->get('user')
        ) {
            return $user;
        }
    }
}
