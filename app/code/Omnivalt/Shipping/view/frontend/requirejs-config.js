var config = {
    /*
    "map": {
        "*": {
            "Magento_Checkout/js/model/shipping-save-processor/default" : "Omnivalt_Shipping/js/shipping-save-processor-default-override",
        }
    },
     * 
     */
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Omnivalt_Shipping/js/action/mixin/set-shipping-information-mixin': true
            }
        }
    },
    paths: {
        leaflet: 'https://unpkg.com/leaflet@1.6.0/dist/leaflet'
    },
    shim: {
        leaflet: {
            exports: 'L'
        }
    }
};