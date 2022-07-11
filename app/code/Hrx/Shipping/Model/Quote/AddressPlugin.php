<?php
namespace Hrx\Shipping\Model\Quote;

use Magento\Quote\Model\Quote\Address;
use Hrx\Shipping\Model\Carrier;

class AddressPlugin
{
    /**
     * Hook into setShippingMethod.
     * As this is magic function processed by __call method we need to hook around __call
     * to get the name of the called method. after__call does not provide this information.
     *
     * @param Address $subject
     * @param callable $proceed
     * @param string $method
     * @param mixed $vars
     * @return Address
     */
    public function around__call($subject, $proceed, $method, $vars)
    {
    	
        $result = $proceed($method, $vars);
        
        if ($method == 'setShippingMethod'
            && $vars[0] == Carrier::CODE.'_PARCEL_TERMINAL'
            && $subject->getExtensionAttributes()
            && $subject->getExtensionAttributes()->getHrxParcelTerminal()
        ) {
            $subject->setHrxParcelTerminal($subject->getExtensionAttributes()->getHrxParcelTerminal());
        }
        elseif (
            $method == 'setShippingMethod'
            && $vars[0] != Carrier::CODE.'_PARCEL_TERMINAL'
        ) {
            //reset office when changing shipping method
            $subject->setHrxParcelTerminal(0);
        }
        return $result;

    }
}