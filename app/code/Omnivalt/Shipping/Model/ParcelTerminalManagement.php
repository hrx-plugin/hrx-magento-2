<?php

namespace Omnivalt\Shipping\Model;

use Omnivalt\Shipping\Api\ParcelTerminalManagementInterface;
use Omnivalt\Shipping\Api\Data\ParcelTerminalInterfaceFactory;
use Omnivalt\Shipping\Model\Carrier;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\Dir;

class ParcelTerminalManagement implements ParcelTerminalManagementInterface
{
    protected $parcelTerminalFactory;

    /**
     * OfficeManagement constructor.
     * @param OfficeInterfaceFactory $officeInterfaceFactory
     */
    public function __construct(ParcelTerminalInterfaceFactory $parcelTerminalInterfaceFactory)
    {
        $this->parcelTerminalFactory = $parcelTerminalInterfaceFactory;
    }

    /**
     * Get offices for the given postcode and city
     *
     * @param string $postcode
     * @param string $limit
     * @param string $country
     * @param string $group
     * @return \Omnivalt\Shipping\Api\Data\OfficeInterface[]
     */
    public function fetchParcelTerminals($group, $city, $country )
    {
        $result = array();
        $result_city = array();
        $parser = new Parser();
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Module\Dir\Reader $reader */
        $configReader = $om->get('Magento\Framework\Module\Dir\Reader');
        $locationFile = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Omnivalt_Shipping') . '/location.xml';
        //$omnivalt_carrier = new Carrier();
        //$terminals = Carrier::getCode('terminal');
        $locationsXMLArray = $parser->load($locationFile)->xmlToArray();
        $locations = array();
        if ($group){
          foreach($locationsXMLArray['LOCATIONS']['_value']['LOCATION'] as $loc_data ){
              if ($country != $loc_data['A0_NAME'])
                  continue;
              $parcelTerminal = $this->parcelTerminalFactory->create();
              $parcelTerminal->setZip($loc_data['ZIP']);
              $parcelTerminal->setName($loc_data['NAME']);
              $parcelTerminal->setLocation($loc_data['A2_NAME']);
              $terminalArray = array('zip'=>$loc_data['ZIP'],'name'=>$loc_data['NAME'],'location'=>$loc_data['A2_NAME']);
              if (!isset($result[$loc_data['A1_NAME']])){
                  $city_object = array('name' => $loc_data['A1_NAME'],'terminals'=> array());
                  $result[$loc_data['A1_NAME']] = $city_object;
              }
              $result[$loc_data['A1_NAME']]['terminals'][] = $terminalArray;
          }
        } else {
          foreach($locationsXMLArray['LOCATIONS']['_value']['LOCATION'] as $loc_data ){
            if ($country != $loc_data['A0_NAME'])
                continue;
            $parcelTerminal = $this->parcelTerminalFactory->create();
            $parcelTerminal->setZip($loc_data['ZIP']);
            $parcelTerminal->setName($loc_data['NAME']);
            $parcelTerminal->setLocation($loc_data['A2_NAME']);
            $result[] = $parcelTerminal;
          }
        }
        return $result;
    }
}