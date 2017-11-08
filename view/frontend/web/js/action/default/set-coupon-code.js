/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Voucherify_Integration/js/model/payment/voucher-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    ko,
    $,
    quote,
    urlManager,
    errorProcessor,
    messageContainer,
    storage,
    $t,
    getPaymentInformationAction,
    totals,
    fullScreenLoader
) {
    'use strict';

    return function (couponCode, isApplied, isOriginal) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.getApplyCouponUrl(couponCode(), quoteId),
            message = $t('Your coupon was successfully applied.');

        fullScreenLoader.startLoader();

        return storage.put(
            url,
            {},
            false
        ).done(function (response) {
            var deferred;

            if (response) {
                deferred = $.Deferred();
                isOriginal(true);
                isApplied(true);
                totals.isLoading(true);
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                });
                messageContainer.addSuccessMessage({
                    'message': message
                });
            }
        }).fail(function (response) {
                couponCode('');
                isApplied(false);
                fullScreenLoader.stopLoader();
                totals.isLoading(false);
                messageContainer.addErrorMessage({
                    'message': $t("Your voucher has not been accepted thus a discount will not be applied.")
                });
        });
    };
});
