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
		 * @returns {Array}
	 	 */
		getCardTypes: function() {return _.keys(this.getCcAvailableTypes());},
		/** @returns {String} */
		getCode: function() {return this.code;},
		/**
		 * 2016-03-02
		 * @return {Object}
		*/
		initialize: function() {
			this._super();
			Stripe.setPublishableKey(this.config('publishableKey'));
			return this;
		},
		/**
		 * @returns {exports.initObservable}
		 */
		initObservable: function() {
			this._super();
			return this;
		},
		pay: function() {
			var _this = this;
			// 2016-03-02
			// https://stripe.com/docs/custom-form#step-2-create-a-single-use-token
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
