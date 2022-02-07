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
    
    protected $omnivaltCarrier;
    
    public function __construct(
        Carrier $omnivaltCarrier
    ) {
        $this->omnivaltCarrier = $omnivaltCarrier;
        parent::contruct();
    }
    
    public function getCurrentTerminal(){
        return $this->getAddress()->getOmnivaltParcelTerminal();
    }
    
    public function getTerminals()
    {
        $rate = $this->getActiveMethodRate();
        $parcel_terminals = $this->omnivaltCarrier->getTerminals($this->getAddress()->getCountryId());
        return $parcel_terminals;
    } 
    
}