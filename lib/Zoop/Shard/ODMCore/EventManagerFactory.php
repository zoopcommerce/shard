<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class EventManagerFactory implements FactoryInterface
{

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get($serviceLocator->get('manifest')->getDocumentManager())->getEventManager();
    }
}
