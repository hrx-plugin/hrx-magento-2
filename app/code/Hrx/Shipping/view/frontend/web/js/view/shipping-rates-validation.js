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
        'Hrx_Shipping/js/model/shipping-rates-validator',
        'Hrx_Shipping/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        hrxShippingRatesValidator,
        hrxShippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('hrx', hrxShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('hrx', hrxShippingRatesValidationRules);
        return Component;
    }
);
