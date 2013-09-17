<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

interface ObjectManagerAwareInterface
{
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setObjectManager(ObjectManager $objectManager);

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getObjectManager();
}
