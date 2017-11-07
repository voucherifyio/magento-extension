define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/error-processor',
    'Voucherify_Integration/js/model/payment/voucher-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Customer/js/model/customer',
    'Voucherify_Integration/js/action/default/set-coupon-code',
    'Voucherify_Integration/js/lib/voucherify'
], function (ko, $, quote, errorProcessor, messageContainer, storage, $t, getPaymentInformationAction,
             totals, fullScreenLoader, customer, setCouponCodeAction
) {
    'use strict';

    var config = window.checkoutConfig.voucherify;
    var quoteId = window.checkoutConfig.quoteData.entity_id;

    //generate items array for voucherify
    function getQuoteItems () {
        var quoteItems = quote.getItems();
        var items = [];
        for (var item in quoteItems) {
            items.push({
                "sku_id": quoteItems[item].sku,
                "quantity": quoteItems[item].qty
            });
        }
        return items;
    }

    function getAmount () {
        var totals = quote.totals();
        var amount;
        switch (config.behaviour.apply_source_type) {
            case "including_shipping":
                amount = totals.subtotal + (totals.shipping_amount - totals.shipping_discount_amount);
                break;
            case "including_tax":
                amount = totals.subtotal + totals.tax_amount;
                break;
            case "including_shipping_tax":
                amount = totals.subtotal + (totals.shipping_amount - totals.shipping_discount_amount) + totals.tax_amount;
                break;
            case "subtotal":
            default :
                amount = totals.subtotal;
        }
        return amount * 100;
    }

    function getCustomer () {
        return {
            "source_id": (quote.guestEmail != null)?quote.guestEmail:customer.customerData.email,
            "email": (quote.guestEmail != null)?quote.guestEmail:customer.customerData.email,
            "name": quote.billingAddress().firstname + " " + quote.billingAddress().lastname,
            "phone": quote.billingAddress().telephone
        };
    }

    function apply (code, discount, isApplied, isOriginal) {
        discount.voucher_code = code;
        storage.put(
            "rest/default/V1/voucherify/apply/cart/"+quoteId,
            JSON.stringify(discount),
            false
        ).done(function (response) {
            if (response) {
                var deferred = $.Deferred();
                totals.isLoading(true);
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    isApplied(true);
                    isOriginal(false);
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                });
                messageContainer.addSuccessMessage({
                    'message': $t("Your coupon was successfully applied.")
                });
            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            isApplied(false);
            errorProcessor.process(response, messageContainer);
        });
    }


    function applyGift(code, gift, isApplied, isOriginal ) {
        var discount = {
            "voucher_code": code,
            "type": "GIFT",
            "amount_off": gift.balance
        };

        storage.put(
            "rest/default/V1/voucherify/apply/cart/"+quoteId,
            JSON.stringify(discount),
            false
        ).done(function (response) {
                if (response) {
                    var deferred = $.Deferred();
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        isApplied(true);
                        isOriginal(false);
                        fullScreenLoader.stopLoader();
                        totals.isLoading(false);
                    });
                    messageContainer.addSuccessMessage({
                        'message': $t("Your coupon was successfully applied.")
                    });
                }
            }).fail(function (response) {
                fullScreenLoader.stopLoader();
                totals.isLoading(false);
                isApplied(false);
                errorProcessor.process(response, messageContainer);
            });
    }

    return function (voucherCode, isApplied, isOriginal) {

        fullScreenLoader.startLoader();

        var params = {
            "code": voucherCode(),
            "amount": getAmount(),
            "items": getQuoteItems(),
            "customer": getCustomer()
        };

        if(config.clientSide.apiId == null || config.clientSide.apiId == '' || config.clientSide.secretKey == null || config.clientSide.secretKey == ''){
            fullScreenLoader.stopLoader();
            isApplied(false);
            voucherCode('');
            messageContainer.addErrorMessage({
                'message': $t("Voucherify wasn't properly configured")
            });
            return;
        }

        Voucherify.initialize(
            config.clientSide.apiId,
            config.clientSide.secretKey
        );

        Voucherify.validate(params, function callback (response) {
            if (response.valid) {
                if(response.gift != undefined) {
                    applyGift(response.code, response.gift, isApplied, isOriginal);
                } else if (response.discount != undefined) {
                    if (response.discount.type == "AMOUNT" && params.amount <= response.discount.amount_off) {
                        messageContainer.addErrorMessage({
                            'message': $t("The discount amount is bigger than your cart total.")
                        });
                        fullScreenLoader.stopLoader();
                    } else {
                        apply(response.code, response.discount, isApplied, isOriginal);
                    }
                }

            } else {
                fullScreenLoader.stopLoader();
                setCouponCodeAction(voucherCode, isApplied, isOriginal);
            }
        });
    };
});
