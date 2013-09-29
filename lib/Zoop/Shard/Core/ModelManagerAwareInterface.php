<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Core;

interface ModelManagerAwareInterface
{
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setModelManager(ModelManager $modelManager);

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getModelManager();
}
