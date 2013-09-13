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

    public function setAccessController(AccessController $accessController)
    {
        $this->accessController = $accessController;
    }

    public function getAccessController()
    {
        return $this->accessController;
    }

    /**
     *
     * @param  \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $targetDocument
     * @return array
     */
    public function addFilterCriteria(ClassMetadata $metadata)
    {
        $accessController = $this->accessController;
        $result = $accessController->areAllowed([Actions::READ], $metadata);

        if ($result->hasCriteria()) {
            if ($result->getAllowed()) {
                return $result->getNew();
            } else {
                $critiera = [];
                foreach ($result->getNew() as $field => $value) {
                    if ($value instanceof \MongoRegex) {
                        $critiera[$field] = ['$not' => $value];
                    } else {
                        $critiera[$field] = ['$ne' => $value];
                    }
                }

                return $critiera;
            }
        } else {
            if ($result->getAllowed()) {
                return []; //allow read
            } else {
                return [$metadata->identifier => ['$exists' => false]]; //deny read
            }
        }
    }
}
