define([
	'Df_StripeClone/main', 'https://js.stripe.com/v2/'
], function(parent) {'use strict'; return parent.extend({
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
	 *
	 * Не стал делать реализацию на сервере, потому что там меня не устраивал
	 * порядок следования платёжных систем (первой была «American Express»)
	 * https://github.com/magento/magento2/blob/cf7df72/app/code/Magento/Payment/etc/payment.xml#L10-L44
	 * А изменить этот порядок коротко не получается:
	 * https://github.com/magento/magento2/blob/487f5f45/app/code/Magento/Payment/Model/CcGenericConfigProvider.php#L105-L124
	 * 
	 * 2017-02-05
	 * The bank card network codes: https://mage2.pro/t/2647
	 *
	 * @returns {String[]}
	 */
	getCardTypes: function() {return(
		['VI', 'MC', 'AE'].concat(!this.config('isUS') ? [] : ['JCB', 'DI', 'DN'])
	);},
	/**
	 * 2016-03-02
	 * @override
	 * @see Df_Payment/card::initialize()
	 * https://github.com/mage2pro/core/blob/2.4.21/Payment/view/frontend/web/card.js#L77-L110
	 * @returns {exports}
	*/
	initialize: function() {
		this._super();
		Stripe.setPublishableKey(this.publicKey());
		return this;
	},
    /**
	 * 2017-02-16
	 * @override
	 * @see Df_StripeClone/main::tokenCheckStatus()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L8-L15
	 * @used-by Df_StripeClone/main::placeOrder()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L75
	 * @param {Number} status
	 * @returns {Boolean}
	 */
	tokenCheckStatus: function(status) {return 200 === status;},
    /**
	 * 2017-02-16
	 * @override
	 * @see https://github.com/mage2pro/core/blob/2.0.11/StripeClone/view/frontend/web/main.js?ts=4#L21-L29
	 * @used-by Df_StripeClone/main::placeOrder()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L73
	 * @param {Object} params
	 * @param {Function} callback
	 */
	tokenCreate: function(params, callback) {Stripe.card.createToken(params, callback);},
    /**
	 * 2017-02-16
	 * https://stripe.com/docs/api#errors
	 * @override
	 * @see https://github.com/mage2pro/core/blob/2.0.11/StripeClone/view/frontend/web/main.js?ts=4#L31-L39
	 * @used-by placeOrder()
	 * @param {Object|Number} status
	 * @param {Object} resp
	 * @returns {String}
	 */
	tokenErrorMessage: function(status, resp) {return resp.error.message;},
    /**
	 * 2017-02-16
	 * @override
	 * @see https://github.com/mage2pro/core/blob/2.0.11/StripeClone/view/frontend/web/main.js?ts=4#L41-L48
	 * @used-by placeOrder()
	 * @param {Object} resp
	 * @returns {String}
	 */
	tokenFromResponse: function(resp) {return resp.id;},
    /**
	 * 2017-02-16
	 * @override
	 * @see Df_StripeClone/main::tokenParams()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L42-L48
	 * @used-by Df_StripeClone/main::placeOrder()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L73
	 * @returns {Object}
	 */
	tokenParams: function() {return {
		cvc: this.creditCardVerificationNumber()
		,exp_month: this.creditCardExpMonth()
		,exp_year: this.creditCardExpYear()
		// 2017-02-16
		// https://stripe.com/docs/stripe.js#card-createToken
		,name: this.cardholder()
		,number: this.creditCardNumber()
	};}
});});