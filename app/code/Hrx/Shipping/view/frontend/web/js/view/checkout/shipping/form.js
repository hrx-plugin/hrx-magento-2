define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'Hrx_Shipping/js/view/checkout/shipping/parcel-terminal-service',
    'mage/translate',
    'Hrx_Shipping/js/hrx-data',
    'leaflet',
    'Hrx_Shipping/js/hrx'
], function ($, ko, Component, quote, shippingService, parcelTerminalService, t, hrxData) {
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
            var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
            
            if (selectedMethod == 'hrx_parcel_terminal') {
                $('#hrx-select-terminal').first().show();
            } else {
                $('#hrx-select-terminal').first().hide();
            }
        },
        moveSelect: function () {
            var hrx_last_selected_terminal = '';
            if ($('#hrx-select-terminal select').length > 0){
                hrx_last_selected_terminal = $('#hrx-select-terminal select').val();
            }
            if ($('#onepage-checkout-shipping-method-additional-load .hrx-terminal-list').length > 0){
                $('#checkout-shipping-method-load input:radio:not(.bound)').addClass('bound').bind('click', this.hideSelect());
                if ($('#checkout-shipping-method-load .hrx-terminal-list').html() !=  $('#onepage-checkout-shipping-method-additional-load .hrx-terminal-list').html()){
                    $('#hrx-select-terminal').remove();
                }
                
                if ($('#checkout-shipping-method-load .hrx-terminal-list').length == 0){
                    var terminal_list = $('#onepage-checkout-shipping-method-additional-load .hrx-hrx-terminal-list-wrapper div');
                    var row = $.parseHTML('<tr><td colspan = "4" style = "border-top: none; padding-top: 0px"></td></tr>');
                    if ($('#s_method_hrx_parcel_terminal').length > 0){
                        var move_after = $('#s_method_hrx_parcel_terminal').parents('tr'); 
                    } else if ($('#label_method_parcel_terminal_hrx').length > 0){
                        var move_after = $('#label_method_parcel_terminal_hrx').parents('tr'); 
                    }
                    var cloned =  terminal_list.clone(true);
                    if ($('#hrx-select-terminal').length == 0){
                        $('<tr id = "hrx-select-terminal" ><td colspan = "4" style = "border-top: none; padding-top: 0px"></td></tr>').insertAfter(move_after);
                    }
                    cloned.appendTo($('#hrx-select-terminal td'));
                }
            }

            
            if (hrxData.getPickupPoint()){
                $('#hrx-select-terminal select').val(hrxData.getPickupPoint());
            }
            
            if($('#hrxModal').length > 0 && $('.hrx-terminals-list').length == 0){
                if ($('#hrx-select-terminal select option').length>0){
                    var hrxdata = [];
                    hrxdata.hrx_plugin_url = require.toUrl('Hrx_Shipping/css/');
                    hrxdata.hrx_current_country = quote.shippingAddress().countryId;
                    hrxdata.text_select_terminal = $.mage.__('Select terminal');
                    hrxdata.text_search_placeholder = $.mage.__('Enter postcode');
                    hrxdata.not_found = $.mage.__('Place not found');
                    hrxdata.text_enter_address = $.mage.__('Enter postcode / address');
                    hrxdata.text_show_in_map = $.mage.__('Show in map');
                    hrxdata.text_show_more = $.mage.__('Show more');
                    hrxdata.postcode = quote.shippingAddress().postcode;
                    $('#hrx-select-terminal select').hrx({hrxdata:hrxdata});
                }
            }
            if (typeof hrx_last_selected_terminal === 'undefined') {
                var hrx_last_selected_terminal = '';
            }

            $('#checkout-step-shipping_method').off('change', '#hrx-select-terminal select').on('change', '#hrx-select-terminal select', function () {
                hrxData.setPickupPoint($(this).val());
                
            });
        },
        initObservable: function () {
            this._super();
            this.showParcelTerminalSelection = ko.computed(function() {
                this.moveSelect();
                return this.parcelTerminals().length != 0
            }, this);

            this.selectedMethod = ko.computed(function() {
                this.moveSelect();
                var method = quote.shippingMethod();
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                return selectedMethod;
            }, this);

            quote.shippingMethod.subscribe(function(method) {
                this.moveSelect();
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                if (selectedMethod == 'hrx_parcel_terminal') {
                    this.reloadParcelTerminals();
                }
            }, this);

            this.selectedParcelTerminal.subscribe(function(terminal) {
                /*
                //not needed on one step checkout, is done from overide
                if (quote.shippingAddress().extensionAttributes == undefined) {
                    quote.shippingAddress().extensionAttributes = {};
                }
                quote.shippingAddress().extensionAttributes.hrx_parcel_terminal = terminal;
                */
            });

            return this;
        },

        setParcelTerminalList: function(list) {
            this.parcelTerminals(list);
            this.moveSelect();
        },
        
        reloadParcelTerminals: function() {
            parcelTerminalService.getParcelTerminalList(quote.shippingAddress(), this, 1);
            this.moveSelect();
        },

        getParcelTerminal: function() {
            var parcelTerminal;
            if (this.selectedParcelTerminal()) {
                for (var i in this.parcelTerminals()) {
                    var m = this.parcelTerminals()[i];
                    if (m.name == this.selectedParcelTerminal()) {
                        parcelTerminal = m;
                    }
                }
            }
            else {
                parcelTerminal = this.parcelTerminals()[0];
            }

            return parcelTerminal;
        },

        initSelector: function() {
            var startParcelTerminal = this.getParcelTerminal();
        }
    });
});