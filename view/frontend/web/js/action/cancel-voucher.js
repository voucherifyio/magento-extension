define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/error-processor',
    'QS_Voucherify/js/model/payment/voucher-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'QS_Voucherify/js/action/default/cancel-coupon',
    'QS_Voucherify/js/lib/voucherify'
], function (ko, $, errorProcessor, messageContainer, storage, $t, getPaymentInformationAction,
             totals, fullScreenLoader, cancelCouponAction
) {
    'use strict';

    var quoteId = window.checkoutConfig.quoteData.entity_id;

    return function (voucherCode, isApplied, isOriginal) {

        if (isOriginal()) {
            cancelCouponAction(voucherCode, isApplied)
        } else {
            fullScreenLoader.startLoader();
            storage.delete(
                "rest/default/V1/voucherify/delete/cart/"+quoteId,
                {},
                false
            ).done(function (response) {
                if (response) {
                    var deferred = $.Deferred();
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        fullScreenLoader.stopLoader();
                        totals.isLoading(false);
                        voucherCode('');
                        isApplied(false);
                        messageContainer.addSuccessMessage({
                            'message': $t("Your coupon was successfully removed.")
                        });
                    });
                }
            }).fail(function (response) {
                fullScreenLoader.stopLoader();
                totals.isLoading(false);
                errorProcessor.process(response, messageContainer);
            });
        }


    };
});
