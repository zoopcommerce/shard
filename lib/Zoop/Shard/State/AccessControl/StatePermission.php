<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State\AccessControl;

use Zoop\Shard\AccessControl\AllowedResult;

use Zoop\Shard\AccessControl\PermissionInterface;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class StatePermission implements PermissionInterface
{
    protected $roles;

    protected $allow;

    protected $deny;

    protected $states;

    protected $stateField;

    public function __construct(array $roles, array $allow, array $deny, array $states, $stateField)
    {
        $this->roles = array_map([$this, 'roleToRegex'], $roles);
        $this->allow = array_map([$this, 'actionToRegex'], $allow);
        $this->deny  = array_map([$this, 'actionToRegex'], $deny);
        $this->states = array_map([$this, 'stateToRegex'], $states);
        $this->stateField = (string) $stateField;
    }

    protected function roleToRegex($string)
    {
        return '/^' . str_replace(PermissionInterface::WILD, '[a-zA-Z0-9_:-]*', $string) . '$/';
    }

    protected function actionToRegex($string)
    {
        return '/^' . str_replace(PermissionInterface::WILD, '[a-zA-Z0-9_:-]*', $string) . '$/';
    }

    protected function stateToRegex($string)
    {
        if (! strpos(self::WILD, $string)) {
            return '/^' . str_replace(PermissionInterface::WILD, '[a-zA-Z0-9_:-]*', $string) . '$/';
        }
        return $string;
    }

    /**
     * Will test if a user with the supplied roles can do ALL the supplied actions.
     *
     * @param array $roles
     * @param array $action
     * @return \Zoop\Shard\AccessControl\IsAllowedResult
     */
    public function areAllowed(array $testRoles, array $testActions)
    {
        //only check allow and deny if there is at least one matching role
        if (count($testRoles) == 0) {
            $testRoles = [''];
        }
        $roleMatch = false;
        foreach ($this->roles as $role) {
            if (count(
                array_filter(
                    $testRoles,
                    function ($testRole) use ($role) {
                    return preg_match($role, $testRole);
                    }
                )
            ) > 0
            ) {
                $roleMatch = true;
                break;
            }
        }

        if (!$roleMatch) {
            //Permission is neither explicitly allowed or denied.
            return new AllowedResult;
        }

        //check allow
        $allowMatches = 0;
        //check each testAction in turn
        foreach ($testActions as $testAction) {
            $allowMatch = count(
                array_filter(
                    $this->allow,
                    function ($action) use ($testAction) {
                        //first check that action matches at least one allow
                        return preg_match($action, $testAction);
                    }
                )
            ) > 0;

            $denyMatch = count(
                array_filter(
                    $this->deny,
                    function ($action) use ($testAction) {
                        //second check that action does not match any deny
                        return preg_match($action, $testAction);
                    }
                )
            ) > 0;

            if ($denyMatch) {
                //one or more actions are explicitly denied
                return new AllowedResult(false, [], [$this->stateField => $this->mongoRegex($this->states)]);
            }
            if ($allowMatch) {
                $allowMatches++;
            }
        }
        if ($allowMatches == count($testActions)) {
            //all actions are explicitly allowed
            return new AllowedResult(true, [], [$this->stateField => $this->mongoRegex($this->states)]);
        }

        //Permission is neither explicitly allowed or denied.
        return new AllowedResult;
    }

    protected function mongoRegex(array $states)
    {
        $result = [];
        foreach ($states as $state) {
            if (!strpos(PermissionInterface::WILD, $state)) {
                $result[] = new \MongoRegex($state);
            } else {
                $result[] = $state;
            }
        }

        if (count($result) == 1) {
            return $result[0];
        } else {
            return ['$or' => $result];
        }
    }
}
