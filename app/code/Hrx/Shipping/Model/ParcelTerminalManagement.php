<?php

namespace Hrx\Shipping\Model;

use Hrx\Shipping\Api\ParcelTerminalManagementInterface;
use Hrx\Shipping\Api\Data\ParcelTerminalInterfaceFactory;
use Hrx\Shipping\Model\Carrier;

class ParcelTerminalManagement implements ParcelTerminalManagementInterface
{

    protected $parcelTerminalFactory;
    protected $hrxCarrier;

    /**
     * OfficeManagement constructor.
     * @param ParcelTerminalInterfaceFactory $parcelTerminalInterfaceFactory
     * @param Carrier $hrxCarrier
     */

    public function __construct(ParcelTerminalInterfaceFactory $parcelTerminalInterfaceFactory, Carrier $hrxCarrier) {
        $this->parcelTerminalFactory = $parcelTerminalInterfaceFactory;
        $this->hrxCarrier = $hrxCarrier;
    }

    /**
     * Get offices for the given postcode and city
     *
     * @param string $group
     * @param string $city
     * @param string $country
     * @return Array
     */
    public function fetchParcelTerminals($country) {
        $result = array();

        $locationsArray = $this->hrxCarrier->getTerminals($country);
        
            foreach ($locationsArray as $loc_data) {
                if ($country != $loc_data['country']) {
                    continue;
                }
                $terminalArray = array(
                    'id' => $loc_data['terminal_id'],
                    'zip' => $loc_data['postcode'],
                    'name' => $loc_data['address'] . ', ' . $loc_data['city'] . ', ' . $loc_data['country'],
                    'location' => $loc_data['address'],
                    'y' => $loc_data['latitude'],
                    'x' => $loc_data['longitude'],
                    'comment' => '',
                    'city' => $loc_data['city'],
                    'country' => $loc_data['country'],
                );
                if (!isset($result[$loc_data['city']])) {
                    $city_object = array('name' => $loc_data['city'], 'terminals' => array());
                    $result[$loc_data['city']] = $city_object;
                }
                $result[$loc_data['city']]['terminals'][] = $terminalArray;
            }
        return $result;
    }

}
