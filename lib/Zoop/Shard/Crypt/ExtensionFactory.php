<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Crypt;

use Zoop\Shard\AbstractExtensionFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ExtensionFactory extends AbstractExtensionFactory
{
    protected $extensionServiceName = 'extension.crypt';

    /**
     *
     * @param  \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Extension($this->getConfig($serviceLocator));
    }
}
