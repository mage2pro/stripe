define ([
	'jquery'
	, 'df'
	, 'Dfe_Stripe/validator'
	, 'Magento_Payment/js/view/payment/cc-form'
], function($, df, validator, Component) {
	'use strict';
	return Component.extend({
		defaults: {
			active: false
			,clientConfig: {id: 'dfe-stripe'}
			,code: 'dfe_stripe'
			,template: 'Dfe_Stripe/item'
		},
		imports: {onActiveChange: 'active'},
		/** @returns {Object} */
		getAddress: function() {
			var result = quote.billingAddress();
			if (!result) {
				result = quote.shippingAddress();
			}
			return result;
		},
		/** @returns {String} */
		getCode: function() {return this.code;},
		/**
		 * @param {String} field
		 * @returns {String}
		 */
		getSelector: function (field) {return '#' + this.getCode() + '_' + field;},
		/**
		 * Set list of observable attributes
		 * @returns {exports.initObservable}
		 */
		initObservable: function() {
			validator.setConfig(window.checkoutConfig.payment[this.getCode()]);
			this._super().observe(['active']);
			return this;
		},
		/** @returns {Boolean} */
		isActive: function() {
			var active = this.getCode() === this.isChecked();
			this.active(active);
			return active;
		},
		placeOrderClick: function () {
			if (this.validateCardType()) {
				$(this.getSelector('submit')).trigger('click');
			}
		}
	});
});
