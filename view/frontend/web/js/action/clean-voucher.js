define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals'
], function (ko, $, fullScreenLoader, getPaymentInformationAction, totals) {
    'use strict';

    return function (voucherCode, isApplied) {
        var deferred = $.Deferred();
        fullScreenLoader.startLoader();
        totals.isLoading(true);
        getPaymentInformationAction(deferred);
        $.when(deferred).done(function () {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            voucherCode('');
            isApplied(false);
        });
    };
});
