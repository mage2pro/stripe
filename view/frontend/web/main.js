define([
	'Df_StripeClone/main', 'Magento_Checkout/js/model/quote', 'https://js.stripe.com/v2/'
], function(parent, quote) {'use strict'; return parent.extend({
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
	 * 2017-08-31
	 * Note 1. https://stripe.com/docs/stripe.js/v2#card-createToken
	 * Note 2. `I want to pass the customer's billing address to the createToken() Stripe.js method`
	 * https://mage2.pro/t/4412
	 * Note 3. `Use rules to automatically block payments or place them in review`:
	 * https://stripe.com/docs/disputes/prevention#using-rules
	 * @override
	 * @see Df_StripeClone/main::tokenParams()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L42-L48
	 * @used-by Df_StripeClone/main::placeOrder()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L73
	 * @returns {Object}
	 */
	tokenParams: function() {
		/**
		 * 2017-08-31
		 * Note 1.
		 * An address looks like:
		 *	{
		 *		"city": "Rio de Janeiro",
		 *		"countryId": "BR",
		 *		"customerAddressId": "7",
		 *		"customerId": "1",
		 *		"firstname": "Dmitry",
		 *		"lastname": "Fedyuk",
		 *		"postcode": "22630-010",
		 *		"region": "Rio de Janeiro",
		 *		"regionCode": "RJ",
		 *		"regionId": "502",
		 *		"saveInAddressBook": null,
		 *		"street": ["Av. Lúcio Costa, 3150 - Barra da Tijuca"],
		 *		"telephone": "+55 21 3139-8000",
		 *		"vatId": "11438374798"
		 *	}
		 * @param {Object} a
		 * @param {String=} a.city	«Rio de Janeiro»
		 * @param {String=} a.countryId	«BR»
		 * @param {Number=} a.customerAddressId	«7»
		 * @param {Number=} a.customerId	«1»
		 * @param {String} a.firstname	«Dmitry»
		 * @param {String} a.lastname	«Fedyuk»
		 * @param {String=} a.postcode	«22630-010»
		 * @param {String=} a.region	«Rio de Janeiro»
		 * @param {String=} a.regionCode	«RJ»
		 * @param {Number=} a.regionId	«502»
		 * @param {?Boolean} a.saveInAddressBook	«null»
		 * @param {String[]} a.street	«["Av. Lúcio Costa, 3150 - Barra da Tijuca"]»
		 * @param {String=} a.telephone	«+55 21 3139-8000»
		 * @param {String=} a.vatId	«11438374798»
		 * https://github.com/mage2pro/core/blob/2.11.2/Payment/view/frontend/web/billingAddressChange.js#L14-L55
		 *
		 * Note 2.
		 * quote.billingAddress() always returns an address.
		 * If the «Require the billing address?» option is disabled, and the customer is new,
		 * then Magento will return the shipping address from the previous checkout step as the billing address.
		 */
		var a = quote.billingAddress();
		return {
			address_city: a.city // 2017-08-31 «billing address city», optional.
			,address_country: a.countryId  // 2017-08-31 «billing address country», optional.
			,address_line1: a.street[0]  // 2017-08-31 «billing address line 1», optional.
			,address_line2: a.street[1]  // 2017-08-31 «billing address line 2», optional.
			,address_state: a.region // 2017-08-31 «billing address state», optional.
			,address_zip: a.postcode // 2017-08-31 «billing ZIP code as a string (e.g., "94301")», optional.
			,cvc: this.creditCardVerificationNumber()
			,exp_month: this.creditCardExpMonth()
			,exp_year: this.creditCardExpYear()
			,name: this.cardholder() // 2017-08-31 «cardholder name», optional.
			,number: this.creditCardNumber()
		};
	}
});});