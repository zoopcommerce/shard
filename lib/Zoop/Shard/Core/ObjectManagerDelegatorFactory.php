<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * This factory triggers the ON_BOOTSTRAP event just after an object manager
 * is first created
 */
class ObjectManagerDelegatorFactory implements DelegatorFactoryInterface
{

    protected $objectManager;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {

        if (isset($this->objectManager)) {
            return $this->objectManager;
        } else {
            $this->objectManager = call_user_func($callback);
            $this->objectManager->getEventManager()->dispatchEvent(Events::BOOTSTRAPPED, new BootstrappedEventArgs($this->objectManager, $this->objectManager->getEventManager()));
        }

        return $this->objectManager;
    }
}
