<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Freeze\Filter;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Filter\BsonFilter;

/**
 * When this filter is enabled, all frozen documents will be filtered out
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Freeze extends BsonFilter
{
    /**
     *
     * @var array
     */
    protected $parameters = array('frozen' => false);

    /**
     * Set the filter to return only documents which are not frozen
     */
    public function onlyNotFrozen(){
        $this->parameters['frozen'] = false;
    }

    /**
     * Set the filter to return only documents which are frozen
     */
    public function onlyFrozen(){
        $this->parameters['frozen'] = true;
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $targetMetadata
     * @return array
     */
    public function addFilterCriteria(ClassMetadata $metadata)
    {
        if (isset($metadata->freeze['flag'])) {
            return array($metadata->freeze['flag'] => $this->parameters['frozen']);
        }
        return array();
    }
}
