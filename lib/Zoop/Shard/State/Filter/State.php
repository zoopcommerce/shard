<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\State\Filter;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Filter\BsonFilter;

/**
 * When this filter is enabled, will either filter out all documents
 * not in a list of States, or all documents in a list of States.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class State extends BsonFilter
{

    /**
     *
     * @var array
     */
    protected $parameters = array(
        'includeStateList' => true,
        'states' => []
   );

    /**
     * Set the filter to return only documents which are
     * in the State list
     */
    public function includeStateList(){
        $this->parameters['includeStateList'] = true;
    }

    /**
     * Set the filter to return only documents which are not in the State list
     */
    public function excludeStateList(){
        $this->parameters['includeStateList'] = false;
    }

    /**
     *
     * @param array $states
     */
    public function setStates(array $states){
        $this->parameters['states'] = $states;
    }

    /**
     *
     * @param string $state
     */
    public function addState($state){
        $this->parameters['states'][] = (string) $state;
    }

    /**
     *
     * @param string $state
     */
    public function removeState($state){
        if (isset($this->parameters['states'][$state])){
            unset($this->parameters['states'][$state]);
        }
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $targetMetadata
     * @return array
     */
    public function addFilterCriteria(ClassMetadata $targetMetadata)
    {
        if (isset($targetMetadata->state) &&
            count($this->parameters['states'])
        ) {
            $operator = $this->parameters['includeStateList'] ? '$in' : '$nin';
            return [
                array_keys($targetMetadata->state)[0] => [$operator => $this->parameters['states']]
            ];
        }
        return array();
    }
}
