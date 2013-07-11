<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
abstract class AbstractExtensionFactory implements FactoryInterface
{
    protected $extensionServiceName;

    public function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('manifest')->getExtensionConfigs();

        if (isset($config[$this->extensionServiceName])){
            $config = $config[$this->extensionServiceName];
        } else {
            return [];
        }

        if (is_bool($config)){
            return [];
        }

        return $config;
    }
}
