define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'Hrx_Shipping/js/view/checkout/shipping/parcel-terminal-service',
    'mage/translate',
    'Hrx_Shipping/js/hrx-data',
    'mage/url',
    'Magento_Checkout/js/model/url-builder',
    'leafletmarkercluster',
    'Hrx_Shipping/js/terminal',
    'Hrx_Shipping/js/hrx'
], function ($, ko, Component, quote, shippingService, parcelTerminalService, t, hrxData, url, urlBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Hrx_Shipping/checkout/shipping/form'
        },

        initialize: function (config) {
            this.parcelTerminals = ko.observableArray();
            this.selectedParcelTerminal = ko.observable();
            this._super();
        },
        hideSelect: function () {
            var method = quote.shippingMethod();
            console.log(method);
            var selectedMethod = method !== null ? method.method_code + '_' + method.carrier_code : null;
            if (selectedMethod && selectedMethod.includes('parcel_terminal_hrx')) {
                if ($('#hrx_global_map_container .hrxml-container').length === 0) {
                    var that = this;
                    setTimeout(function() {
                        that.createMap(method);
                      }, 500);
                    
                } else {
                    $('#hrx_global_map_container').first().show();
                }
            } else {
                $('#hrx_global_map_container').first().hide();
            }
        },
        createMap: function (method) {
            this.setData(method);
            if ($('#hrx_global_map_container').length === 0) {
                if ($('#s_method_' + method.carrier_code + '_' + method.method_code).length > 0) {
                    var move_after = $('#s_method_' + method.carrier_code + '_' + method.method_code).parents('tr');
                } else if ($('#label_method_' + method.method_code + '_' + method.carrier_code).length > 0) {
                    var move_after = $('#label_method_' + method.method_code + '_' + method.carrier_code).parents('tr');
                }
                $('<tr id = "hrx_global_map_container" ><td colspan = "4" style = "border-top: none; padding-top: 0px"></td></tr>').insertAfter(move_after);
            }
            $('body').trigger('load-hrx-terminals');  
            $('input[name="hrx_global_terminal"]').on('change', function (){
                hrxData.setPickupPoint($(this).val());
            });
            if (hrxData.getPickupPoint()) {
                preselected_terminal = hrxData.getPickupPoint();
            }
        },
        setData: function(method) {
            var address = quote.shippingAddress();
            var data = method.method_code.replace('_terminal', '').split('_');
            //unset service type
            data.splice(0, 1);
            var identifier = data.join('_');
            window.hrxGlobalSettings = {
                max_distance: 1000,//window.checkoutConfig.hrxGlobalData.distance,
                identifier: identifier,
                country: address.countryId,
                api_url: url.build( urlBuilder.createUrl('/hrx/terminals', {})),    
                city: address.city,
                postcode: address.postcode,
                address: address.street[0],
                hrx_plugin_url: require.toUrl('Hrx_Shipping/css/')
            };
            
            hrxGlobalData.text_select_terminal = $.mage.__('Select terminal');
            hrxGlobalData.text_select_post = $.mage.__('Select post office', 'hrx_global');
            hrxGlobalData.text_search_placeholder = $.mage.__('Enter postcode', 'hrx_global');
            hrxGlobalData.text_not_found = $.mage.__('Place not found', 'hrx_global');
            hrxGlobalData.text_enter_address = $.mage.__('Enter postcode/address', 'hrx_global');
            hrxGlobalData.text_map = $.mage.__('Terminal map', 'hrx_global');
            hrxGlobalData.text_list = $.mage.__('Terminal list', 'hrx_global');
            hrxGlobalData.text_search = $.mage.__('Search', 'hrx_global');
            hrxGlobalData.text_reset = $.mage.__('Reset search', 'hrx_global');
            hrxGlobalData.text_select = $.mage.__('Choose terminal', 'hrx_global');
            hrxGlobalData.text_no_city = $.mage.__('City not found', 'hrx_global');
            hrxGlobalData.text_my_loc = $.mage.__('Use my location', 'hrx_global');
        },
        moveSelect: function () {
            $('#checkout-shipping-method-load input:radio:not(.hrxglobalbound)').addClass('hrxglobalbound').bind('click', this.hideSelect());
        },
        initObservable: function () {
            this._super();
            quote.shippingMethod.subscribe(function (method) {
                this.moveSelect();
                
            }, this);


            return this;
        },

        setParcelTerminalList: function (list) {
            this.parcelTerminals(list);
            this.moveSelect();
        },

        reloadParcelTerminals: function () {
            parcelTerminalService.getParcelTerminalList(quote.shippingAddress(), this, 1);
            this.moveSelect();
        },

        getParcelTerminal: function () {
            var parcelTerminal;
            if (this.selectedParcelTerminal()) {
                for (var i in this.parcelTerminals()) {
                    var m = this.parcelTerminals()[i];
                    if (m.name == this.selectedParcelTerminal()) {
                        parcelTerminal = m;
                    }
                }
            } else {
                parcelTerminal = this.parcelTerminals()[0];
            }

            return parcelTerminal;
        },

        initSelector: function () {
            var startParcelTerminal = this.getParcelTerminal();
        }
    });
});