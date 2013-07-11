<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\AccessControl\Filter;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Filter\BsonFilter;
use Zoop\Shard\AccessControl\Actions;
use Zoop\Shard\AccessControl\AccessController;

/**
 * When this filter is enabled, will filter out all documents
 * the active user does not have permission to read.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ReadAccessControl extends BsonFilter
{

    protected $accessController;

    public function setAccessController(AccessController $accessController) {
        $this->accessController = $accessController;
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $targetDocument
     * @return array
     */
    public function addFilterCriteria(ClassMetadata $metadata)
    {
        $accessController = $this->accessController;
        $result = $accessController->areAllowed([Actions::read], $metadata);

        if ($result->hasCriteria()){
            return $result->getNew();
        } else {
            if ($result->getAllowed()){
                return []; //allow read
            } else {
                return [$metadata->identifier => ['$exists' => false]]; //deny read
            }
        }
    }
}
