/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Omnivalt_Shipping/js/model/shipping-rates-validator',
        'Omnivalt_Shipping/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        omnivaltShippingRatesValidator,
        omnivaltShippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('omnivalt', omnivaltShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('omnivalt', omnivaltShippingRatesValidationRules);
        return Component;
    }
);
