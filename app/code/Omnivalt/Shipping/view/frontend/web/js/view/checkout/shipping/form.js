define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'Omnivalt_Shipping/js/view/checkout/shipping/parcel-terminal-service',
    'mage/translate',
], function ($, ko, Component, quote, shippingService, parcelTerminalService, t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Omnivalt_Shipping/checkout/shipping/form'
        },

        initialize: function (config) {
            this.parcelTerminals = ko.observableArray();
            this.selectedParcelTerminal = ko.observable();
            this._super();
            
        },
        hideSelect: function () {
            var method = quote.shippingMethod();
            var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
            
            if (selectedMethod == 'omnivalt_PARCEL_TERMINAL') {
                $('#onepage-checkout-shipping-method-additional-load .omnivalt-parcel-terminal-list-wrapper').first().show();
            } else {
                $('#onepage-checkout-shipping-method-additional-load .omnivalt-parcel-terminal-list-wrapper').first().hide();
            }
        },
        moveSelect: function () {
            if ($('#onepage-checkout-shipping-method-additional-load .parcel-terminal-list').length > 0){
                $('#checkout-shipping-method-load input:radio:not(.bound)').addClass('bound').bind('click', this.hideSelect());
                if ($('#checkout-shipping-method-load .parcel-terminal-list').html() !=  $('#onepage-checkout-shipping-method-additional-load .parcel-terminal-list').html()){
                    $('#terminal-select-location').remove();
                }
                
                if ($('#checkout-shipping-method-load .parcel-terminal-list').length == 0){
                    var terminal_list = $('#onepage-checkout-shipping-method-additional-load .parcel-terminal-list');
                    var row = $.parseHTML('<tr><td colspan = "4" style = "border-top: none; padding-top: 0px"></td></tr>');
                    var move_after = $('#s_method_omnivalt_PARCEL_TERMINAL').parents('tr'); 
                    var cloned =  terminal_list.clone(true);
                    if ($('#terminal-select-location').length == 0){
                        $('<tr id = "terminal-select-location" ><td colspan = "4" style = "border-top: none; padding-top: 0px"></td></tr>').insertAfter(move_after);
                    }
                    cloned.appendTo($('#terminal-select-location td'));
                }
            }
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
                if (selectedMethod == 'omnivalt_PARCEL_TERMINAL') {
                    this.reloadParcelTerminals();
                }
            }, this);

            this.selectedParcelTerminal.subscribe(function(terminal) {
                /*
                //not needed on one step checkout, is done from overide
                if (quote.shippingAddress().extensionAttributes == undefined) {
                    quote.shippingAddress().extensionAttributes = {};
                }
                quote.shippingAddress().extensionAttributes.omnivalt_parcel_terminal = terminal;
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