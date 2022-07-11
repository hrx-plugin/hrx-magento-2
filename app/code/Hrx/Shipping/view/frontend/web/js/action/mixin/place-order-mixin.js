define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Hrx_Shipping/js/omniva-data'
], function (wrapper, quote, $omnivaData) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, redirectOnSuccess) {
            let selectedShippingMethod = quote.shippingMethod();
            let shippingAddress = quote.shippingAddress();
                
                
            if (selectedShippingMethod.carrier_code !== 'hrx') {
                return originalAction(paymentData, redirectOnSuccess);
            }
                
            let terminal = $omnivaData.getPickupPoint();
                
            if (selectedShippingMethod.method_code === 'parcel_terminal' && !terminal) {
                return originalAction(paymentData, redirectOnSuccess);
            }
                
            if (shippingAddress.extensionAttributes === undefined) {
                shippingAddress.extensionAttributes = {};
            }
                
            if (shippingAddress.extension_attributes === undefined) {
                shippingAddress.extension_attributes = {};
            }
                
            shippingAddress.extensionAttributes.hrx_parcel_terminal = terminal;
            shippingAddress.extension_attributes.hrx_parcel_terminal = terminal;

            return originalAction(paymentData, redirectOnSuccess);
        });
    };
});