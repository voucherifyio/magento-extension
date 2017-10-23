define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'QS_Voucherify/summary/discount'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            return this.getPureValue() != 0;
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            var price = 0;

            if (this.totals() && this.totals()['discount_amount']) {
                price = parseFloat(this.totals()['discount_amount']);
            }
            return price;
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        }
    });
});
