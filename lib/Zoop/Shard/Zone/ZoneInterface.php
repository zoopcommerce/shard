<?php
/**
 * @link       http://zoopcommerce.github.io/common
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\Shard\Zone;

/**
 * Defines methods for a resource aware of zones
 *
 * Zone: Each resource may be assigned to multiple zones, A zone may indicate a business
 * department, geographical area, or such.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
interface ZoneInterface
{
    /**
     * Set all possible zones
     *
     * @param array $zones An array of strings which are zone names
     */
    public function setZones(array $zones);

    /**
     * Add a zone to the existing zone array
     *
     * @param string $zone
     */
    public function addZone($zone);

    /**
     *
     * @param string $zone
     */
    public function removeZone($zone);

    /**
     * Get the zone array
     *
     * @return array
     */
    public function getZones();
}
