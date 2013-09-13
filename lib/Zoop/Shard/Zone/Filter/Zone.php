<?php
/**
 * @link       http://zoopcommerce.github.io/shard
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Zone\Filter;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Filter\BsonFilter;

/**
 * When this filter is enabled, will either filter out all documents
 * not in a list of zones, or all documents in a list of zones.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Zone extends BsonFilter
{

    /**
     *
     * @var array
     */
    protected $parameters = array(
        'includeZoneList' => true,
        'zones' => array()
    );

    /**
     * Set the filter to return only documents which are not
     * in the zone list
     */
    public function includeZoneList()
    {
        $this->parameters['includeZoneList'] = true;
    }

    /**
     * Set the filter to return only documents which are in the zone list
     */
    public function excludeZoneList()
    {
        $this->parameters['includeZoneList'] = false;
    }

    /**
     *
     * @param array $zones
     */
    public function setZones(array $zones)
    {
        $this->parameters['zones'] = array_map(
            function ($zone) {
                return (string) $zone;
            },
            $zones
        );
    }

    /**
     *
     * @param string $zone
     */
    public function addZone($zone)
    {
        $this->parameters['zones'][] = (string) $zone;
    }

    /**
     *
     * @param string $zone
     */
    public function removeZone($zone)
    {
        if (isset($this->parameters['zones'][$zone])) {
            unset($this->parameters['zones'][$zone]);
        }
    }

    /**
     *
     * @param  \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $targetMetadata
     * @return array
     */
    public function addFilterCriteria(ClassMetadata $targetMetadata)
    {
        if (isset($targetMetadata->zones) &&
            count($this->parameters['zones'])
        ) {
            $operator = $this->parameters['includeZoneList'] ? '$in' : '$nin';

            return array(
                $targetMetadata->zones => array($operator => $this->parameters['zones'])
            );
        }

        return array();
    }
}
