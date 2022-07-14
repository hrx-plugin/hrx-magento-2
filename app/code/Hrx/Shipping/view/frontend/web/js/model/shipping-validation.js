define(
        ['mage/translate',
            'Magento_Ui/js/model/messageList',
            'Magento_Checkout/js/model/quote',
            'Hrx_Shipping/js/hrx-data'],
        function ($t, messageList, quote, $hrxData) {
            'use strict';
            return {
                validate: function () {
                    var isValid = true;

                    let selectedShippingMethod = quote.shippingMethod();

                    if (selectedShippingMethod !== null && selectedShippingMethod.carrier_code === 'hrx') {
                        let terminal = $hrxData.getPickupPoint();
                        if (selectedShippingMethod.method_code === 'parcel_terminal' && !terminal) {
                            messageList.addErrorMessage({message: $t('Select Hrx parcel terminal')});
                            isValid = false;
                        }
                    }

                    return isValid;
                }
            }
        }
);
