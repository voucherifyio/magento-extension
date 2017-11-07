define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Voucherify_Integration/js/action/apply-voucher',
    'Voucherify_Integration/js/action/cancel-voucher',
    'Voucherify_Integration/js/action/clean-voucher'
], function ($, ko, Component, quote, applyAction, cancelAction, cleanAction) {
    'use strict';

    ko.bindingHandlers.readonly = {
        update: function (element, valueAccessor) {
            if (valueAccessor()) {
                $(element).attr("readonly", "readonly");
            } else {
                $(element).removeAttr("readonly");
            }
        }
    };

    var voucherData = window.checkoutConfig.quoteData.voucher_data,
        totals = quote.getTotals(),
        voucherCode = ko.observable(null),
        isApplied,
        isOriginal = ko.observable(false);

    if(voucherData!=null && voucherData.voucher_code!=null) {
        voucherCode(voucherData.voucher_code);
    }


    if (totals() && voucherCode() == null) {
        voucherCode(totals()['coupon_code']);
        isOriginal(true);
    }

    isApplied = ko.observable(voucherCode() != null);

    $(document).on('ajaxComplete', function (event, xhr) {
        if (xhr.responseJSON.code == 45689) {
            cleanAction(voucherCode, isApplied);
        }
    });

    return Component.extend({
        defaults: {
            template: 'Voucherify_Integration/payment/voucher'
        },

        voucherCode: voucherCode,

        /**
         * Applied flag
         */
        isApplied: isApplied,

        isOriginal: isOriginal,

        /**
         * Voucher application procedure
         */
        apply: function () {
            if (this.validate()) {
                applyAction(voucherCode, isApplied, isOriginal);
            }
        },

        /**
         * Cancel using voucher
         */
        cancel: function () {
            cancelAction(voucherCode, isApplied, isOriginal);
        },

        /**
         * Voucher validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#voucher-form';
            return ( $(form).validation() && $(form).validation('isValid'));
        }
    });
});
