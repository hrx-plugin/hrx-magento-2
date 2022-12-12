var config = {
    /*
    "map": {
        "*": {
            "Magento_Checkout/js/model/shipping-save-processor/default" : "Hrx_Shipping/js/shipping-save-processor-default-override",
        }
    },
     * 
     */
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Hrx_Shipping/js/action/mixin/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Hrx_Shipping/js/action/mixin/place-order-mixin': true
            }
        }
    },
    paths: {
        leaflet: 'https://unpkg.com/leaflet@1.6.0/dist/leaflet',
        leafletmarkercluster: 'https://unpkg.com/leaflet.markercluster@1.5.1/dist/leaflet.markercluster'
    },
    shim: {
        leaflet: {
            exports: 'L'
        },
        leafletmarkercluster: {
            deps: ['leaflet']
        }
    }
};