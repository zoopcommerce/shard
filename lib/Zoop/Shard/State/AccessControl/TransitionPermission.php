<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State\AccessControl;

use Zoop\Common\State\Transition;
use Zoop\Shard\AccessControl\AllowedResult;
use Zoop\Shard\AccessControl\PermissionInterface;
use Zoop\Shard\Exception\InvalidArgumentException;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class TransitionPermission implements PermissionInterface
{
    protected $roles;

    protected $allow;

    protected $deny;

    protected $state;

    protected $stateField;

    public function __construct(array $roles, array $allow, array $deny, $stateField)
    {
        $this->roles = array_map([$this, 'roleToRegex'], $roles);
        $this->allow = array_map([$this, 'actionToRegex'], $allow);
        $this->deny  = array_map([$this, 'actionToRegex'], $deny);
        $this->stateField = (string) $stateField;
    }

    protected function roleToRegex($string)
    {
        return '/^' . str_replace(PermissionInterface::WILD, '[a-zA-Z0-9_:-]*', $string) . '$/';
    }

    protected function actionToRegex($string)
    {
        $transition = Transition::fromString($string);
        if (! $transition) {
            throw new InvalidArgumentException('Invalid transition passed to TransitonPermission');
        }

        return '/^'
            . str_replace(PermissionInterface::WILD, '[a-zA-Z0-9_:-]*', $transition->getFrom())
            . Transition::ARROW
            . str_replace(PermissionInterface::WILD, '[a-zA-Z0-9_:-]*', $transition->getTo())
            . '$/';
    }

    /**
     * Will test if a user with the supplied roles can do ALL the supplied actions.
     *
     * @param  array                                     $roles
     * @param  array                                     $action
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
            return new AllowedResult; //Permission is neither explicitly allowed or denied.
        }

        //check allow
        $allowMatches = 0;
        foreach ($testActions as $testAction) { //check each testAction in turn
            //first check that action matches at least one allow
            $allowMatch = count(
                array_filter(
                    $this->allow,
                    function ($action) use ($testAction) {
                        return preg_match($action, $testAction);
                    }
                )
            ) > 0;

            //second check that action does not matche any deny
            $denyMatch = count(
                array_filter(
                    $this->deny,
                    function ($action) use ($testAction) {
                        return preg_match($action, $testAction);
                    }
                )
            ) > 0;

            if ($denyMatch) {
                $transition = Transition::fromString($testActions[0]);
                //one or more actions are explicitly denied
                return new AllowedResult(
                    false,
                    [$this->stateField => $this->mongoRegex($transition->getFrom())],
                    [$this->stateField => $this->mongoRegex($transition->getTo())]
                );
            }
            if ($allowMatch) {
                $allowMatches++;
            }
        }
        if ($allowMatches == count($testActions)) {
            $transition = Transition::fromString($testActions[0]);
            //all actions are explicitly allowed
            return new AllowedResult(
                true,
                [$this->stateField => $this->mongoRegex($transition->getFrom())],
                [$this->stateField => $this->mongoRegex($transition->getTo())]
            );
        }

        //Permission is neither explicitly allowed or denied.
        return new AllowedResult;
    }

    protected function mongoRegex($state)
    {
        if (strchr($state, PermissionInterface::WILD)) {
            return new \MongoRegex('/^' . str_replace(PermissionInterface::WILD, '[a-zA-Z0-9_:-]*', $state) . '$/');
        } else {
            return $state;
        }
    }
}
