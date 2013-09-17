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
 * This factory triggers the ON_BOOTSTRAP event just after an model manager
 * is first created
 */
class ModelManagerDelegatorFactory implements DelegatorFactoryInterface
{

    protected $modelManager;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {

        if (isset($this->modelManager)) {
            return $this->modelManager;
        } else {
            $this->modelManager = call_user_func($callback);
            $this->modelManager->getEventManager()->dispatchEvent(Events::BOOTSTRAPPED, new BootstrappedEventArgs($this->modelManager, $this->modelManager->getEventManager()));
        }

        return $this->modelManager;
    }
}
