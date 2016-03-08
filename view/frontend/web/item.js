define ([
	'Magento_Payment/js/view/payment/cc-form'
	,'jquery'
	, 'df'
	, 'mage/translate'
	, 'underscore'
	,'Dfe_Stripe/API'
], function(Component, $, df, $t, _, Stripe) {
	'use strict';
	return Component.extend({
		defaults: {
			active: false
			,clientConfig: {id: 'dfe-stripe'}
			,code: 'dfe_stripe'
			,template: 'Dfe_Stripe/item'
		},
		imports: {onActiveChange: 'active'},
		/**
		 * 2016-03-02
		 * @param {?String} key
		 * @returns {Object}|{*}
	 	 */
		config: function(key) {
			/** @type {Object} */
			var result =  window.checkoutConfig.payment[this.getCode()];
			return !key ? result : result[key];
		},
		/**
		 * 2016-03-01
		 * 2016-03-08
		 * Раньше реализация была такой:
		 * return _.keys(this.getCcAvailableTypes())
		 *
		 * https://support.stripe.com/questions/which-cards-and-payment-types-can-i-accept-with-stripe
		 * «Which cards and payment types can I accept with Stripe?
		 * With Stripe, you can charge almost any kind of credit or debit card:
		 * U.S. businesses can accept
		  		Visa, MasterCard, American Express, JCB, Discover, and Diners Club.
		 * Australian, Canadian, European, and Japanese businesses can accept
		 * 		Visa, MasterCard, and American Express.»
		 * @returns {String[]}
	 	 */
		getCardTypes: function() {
			return ['VI', 'MC', 'AE'].concat(!this.config('isUS') ? [] : ['JCB', 'DI', 'DN']);
		},
		/** @returns {String} */
		getCode: function() {return this.code;},
		/**
		 * 2016-03-06
   		 * @override
   		 */
		getData: function () {
			return {
				method: this.item.method,
				additional_data: {token: this.token}
			};
		},
		/**
		 * 2016-03-02
		 * @return {Object}
		*/
		initialize: function() {
			this._super();
			Stripe.setPublishableKey(this.config('publishableKey'));
			return this;
		},
		pay: function() {
			var _this = this;
			// 2016-03-02
			// https://stripe.com/docs/custom-form#step-2-create-a-single-use-token
			/**
			 * 2016-03-07
			 * https://support.stripe.com/questions/which-cards-and-payment-types-can-i-accept-with-stripe
			 * Which cards and payment types can I accept with Stripe?
			 * With Stripe, you can charge almost any kind of credit or debit card:
			 * U.S. businesses can accept:
			 * 		Visa, MasterCard, American Express, JCB, Discover, and Diners Club.
			 * Australian, Canadian, European, and Japanese businesses can accept:
			 * 		Visa, MasterCard, and American Express.
			 */
			Stripe.card.createToken($('form.dfe-stripe'),
				/**
				 * 2016-03-02
			 	 * @param {Number} status
				 * @param {Object} response
				 */
				function(status, response) {
					//debugger;
					if (200 === status) {
						debugger;
						// 2016-03-02
						// https://stripe.com/docs/custom-form#step-3-sending-the-form-to-your-server
						_this.token = response.id;
						_this.placeOrder();
					}
					else {
						// 2016-03-02
						// https://stripe.com/docs/api#errors
						_this.messageContainer.addErrorMessage({
							'message': $t(response.error.message)
						});
					}
				}
			);
		}
	});
});
