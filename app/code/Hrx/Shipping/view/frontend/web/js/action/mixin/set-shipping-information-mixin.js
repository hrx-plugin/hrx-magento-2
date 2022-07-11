define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Hrx_Shipping/js/omniva-data'
], function($, wrapper, quote, globalMessageList, $t, $omnivaData) {
    'use strict';

    return function(shippingInformationAction) {

        return wrapper.wrap(
            shippingInformationAction,
            function(originalAction) {
                let selectedShippingMethod = quote.shippingMethod();
                let shippingAddress = quote.shippingAddress();
                
                
                if (selectedShippingMethod.carrier_code !== 'hrx') {
                    return originalAction();
                }
                
                let terminal = $omnivaData.getPickupPoint();
                
                if (selectedShippingMethod.method_code === 'parcel_terminal' &&
                    !terminal) {
                    globalMessageList.addErrorMessage(
                        {message: $t('Select Hrx parcel terminal!')});
                    jQuery(window).scrollTop(0);
                    return originalAction();
                }
                
                if (shippingAddress.extensionAttributes === undefined) {
                    shippingAddress.extensionAttributes = {};
                }
                
                if (shippingAddress.extension_attributes === undefined) {
                    shippingAddress.extension_attributes = {};
                }
                
                shippingAddress.extensionAttributes.hrx_parcel_terminal = terminal;
                shippingAddress.extension_attributes.hrx_parcel_terminal = terminal;

                return originalAction();
            });
    };
});
