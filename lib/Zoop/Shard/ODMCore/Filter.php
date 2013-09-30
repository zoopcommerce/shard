<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\ODMCore;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Filter\BsonFilter;
use Doctrine\ODM\MongoDB\Query\CriteriaMerger;
use Zoop\Shard\Core\Events as CoreEvents;
use Zoop\Shard\Core\ReadEventArgs;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Filter extends BsonFilter
{

    protected $eventManager;

    protected $criteriaMerger;

    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function getCriteriaMerger()
    {
        if (!isset($this->criteriaMerger)) {
            $this->criteriaMerger = new CriteriaMerger;
        }

        return $this->criteriaMerger;
    }

    public function setCriteriaMerger($criteriaMerger)
    {
        $this->criteriaMerger = $criteriaMerger;
    }

    /**
     *
     * @param  \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $targetDocument
     * @return array
     */
    public function addFilterCriteria(ClassMetadata $metadata)
    {
        $eventArgs = new ReadEventArgs($metadata, $this->eventManager);
        $this->eventManager->dispatchEvent(CoreEvents::READ, $eventArgs);

        return call_user_func_array([$this->getCriteriaMerger(), 'merge'], $this->replaceRegex($eventArgs->getCriteria()));
    }

    protected function replaceRegex(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists('$regex', $value)) {
                    $array[$key] = new \MongoRegex($value['$regex']);
                } else {
                    $array[$key] = $this->replaceRegex($value);
                }
            }
        }

        return $array;
    }
}
