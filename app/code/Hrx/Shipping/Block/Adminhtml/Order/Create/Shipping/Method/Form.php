<?php

namespace Hrx\Shipping\Block\Adminhtml\Order\Create\Shipping\Method;

use Magento\Quote\Model\Quote\Address\Rate;
use Hrx\Shipping\Model\Carrier;

/**
 * Class Form
 * @package MagePal\CustomShippingRate\Block\Adminhtml\Order\Create\Shipping\Method
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form
{
    
    protected $hrxCarrier;
    
    public function __construct(
        Carrier $hrxCarrier
    ) {
        $this->hrxCarrier = $hrxCarrier;
        parent::contruct();
    }
    
    public function getCurrentTerminal(){
        return $this->getAddress()->getHrxParcelTerminal();
    }
    
    public function getTerminals()
    {
        $rate = $this->getActiveMethodRate();
        $parcel_terminals = $this->hrxCarrier->getTerminals($this->getAddress()->getCountryId());
        return $parcel_terminals;
    } 
    
}