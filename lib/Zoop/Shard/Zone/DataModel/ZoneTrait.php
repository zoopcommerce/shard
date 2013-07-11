<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Zone\DataModel;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotations as Shard;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
trait ZoneTrait {

    /**
     * @ODM\Collection
     * @Shard\Zones
     */
    protected $zones = array();

    /**
     * Set all possible zones
     *
     * @param array $zones An array of strings which are zone names
     */
    public function setZones(array $zones){
        $this->zones = array_map(function($zone){return (string) $zone;}, $zones);
    }

    /**
     * Add a zone to the existing zone array
     *
     * @param string $zone
     */
    public function addZone($zone){
        $this->zones[] = (string) $zone;
    }

    /**
     *
     * @param string $zone
     */
    public function removeZone($zone){
        if(($key = array_search($zone, $this->zones)) !== false)
        {
            unset($this->zones[$key]);
        }
    }

    /**
     * Get the zone array
     *
     * @return array
     */
    public function getZones(){
        return $this->zones;
    }
}
