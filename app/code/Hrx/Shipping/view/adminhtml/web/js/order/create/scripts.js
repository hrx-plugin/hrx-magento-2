define([
    'jquery',
    'Magento_Sales/order/create/scripts'
], function (jQuery) {
    'use strict';


    AdminOrder.prototype.setParcelTerminal  = function(pickup_point) {
              var data = {};
              data['order[shipping_method]'] = 'hrx_PARCEL_TERMINAL';
              data['order[hrx_parcel_terminal]'] = pickup_point;
              this.loadArea(['shipping_method', 'totals', 'billing_method'], true, data);
            };
});