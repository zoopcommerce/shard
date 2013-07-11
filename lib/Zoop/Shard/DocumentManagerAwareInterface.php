<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard;

use Doctrine\ODM\MongoDB\DocumentManager;

interface DocumentManagerAwareInterface
{
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setDocumentManager(DocumentManager $documentManager);

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getDocumentManager();
}
