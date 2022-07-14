define([
    'jquery'
], function ($) {
    'use strict';

    function getForm(url, order_id, terminal_id) {
        return $('<form>', {
            'action': url,
            'method': 'GET'
        }).append($('<input>', {
            'name': 'form_key',
            'value': window.FORM_KEY,
            'type': 'hidden'
        })).append($('<input>', {
            'name': 'order_id',
            'value': order_id,
            'type': 'hidden'
        })).append($('<input>', {
            'name': 'terminal_id',
            'value': terminal_id,
            'type': 'hidden'
        }));
    }

    $('#hrx-change-terminal').on('click',function () {
        var select = $('#hrx_parcel_terminal_list');
        var url = select.attr('data-url');
        var order_id = select.attr('data-order');
        var terminal_id = select.val();
        getForm(url, order_id, terminal_id).appendTo('body').submit();
    });
    
});