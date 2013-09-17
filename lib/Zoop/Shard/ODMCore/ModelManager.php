<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\Common\EventManager;
use Zoop\Shard\Core\ModelManager as CoreModelManager;

/**
 * Extends ClassMetadata to support Shard metadata
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ModelManager extends DocumentManager implements CoreModelManager
{
    /**
     * Creates a new Document that operates on the given Mongo connection
     * and uses the given Configuration.
     *
     * @static
     * @param \Doctrine\MongoDB\Connection|null $conn
     * @param Configuration|null $config
     * @param \Doctrine\Common\EventManager|null $eventManager
     * @return DocumentManager
     */
    public static function create(Connection $conn = null, Configuration $config = null, EventManager $eventManager = null)
    {
        return new ModelManager($conn, $config, $eventManager);
    }
}
