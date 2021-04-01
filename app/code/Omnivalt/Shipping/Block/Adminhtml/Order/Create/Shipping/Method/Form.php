<?php

namespace Omnivalt\Shipping\Block\Adminhtml\Order\Create\Shipping\Method;

use Magento\Quote\Model\Quote\Address\Rate;
use Omnivalt\Shipping\Model\Carrier;

/**
 * Class Form
 * @package MagePal\CustomShippingRate\Block\Adminhtml\Order\Create\Shipping\Method
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form
{
    protected $Omnivalt_carrier;
    
    
    public function getCurrentTerminal(){
        return $this->getAddress()->getOmnivaltParcelTerminal();
    }
    
    public function getTerminals()
    {
        $rate = $this->getActiveMethodRate();
        $parcel_terminals = Carrier::_getOmnivaltTerminals($this->getAddress()->getCountryId());
        return $parcel_terminals;
    } 
    
}