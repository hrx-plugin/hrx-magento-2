define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Omnivalt_Shipping/js/model/shipping-validation'
    ],
    function (Component, additionalValidators, yourValidator) {
        'use strict';
        additionalValidators.registerValidator(yourValidator);
        return Component.extend({});
    }
);
