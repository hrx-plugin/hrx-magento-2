<?php

namespace Hrx\Shipping\Api;

interface ParcelTerminalManagementInterface
{

    /**
     * Find parcel terminals for the customer
     *
     * @param string $group
     * @param string $city
     * @param string $country
     * @return array
     */
    public function fetchParcelTerminals($group, $city, $country );
}