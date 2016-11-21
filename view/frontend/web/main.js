define([
	'Df_Payment/card', 'https://js.stripe.com/v2/'
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
	 * @returns {String[]}
	 */
	getCardTypes: function() {
		return ['VI', 'MC', 'AE'].concat(!this.config('isUS') ? [] : ['JCB', 'DI', 'DN']);
	},
	/**
	 * 2016-03-02
	 * @returns {Object}
	*/
	initialize: function() {
		this._super();
		Stripe.setPublishableKey(this.config('publicKey'));
		return this;
	},
	/**
	 * @override
	 * @see https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Checkout/view/frontend/web/js/view/payment/default.js#L127-L159
	 * @used-by https://github.com/magento/magento2/blob/2.1.0/lib/web/knockoutjs/knockout.js#L3863
	 * @param {this} _this
	*/
	placeOrder: function(_this) {
		if (this.validate()) {
			if (!this.isNewCardChosen()) {
				/**
				 * 2016-08-23
				 * Идентификаторы карт начинаются с приставки «card_»
				 * (например: «card_18lGFRFzKb8aMux1Bmcjsa5L»),
				 * а идентификаторы токенов — с приставки «tok_»
				 * (например: «tok_18lWSWFzKb8aMux1viSqpL5X»),
				 * тем самым по приставке мы можем отличить карты от токенов,
				 * и поэтому для карт и токенов мы можем использовать одну и ту же переменную.
				 */
				this.token = this.currentCard();
				this.placeOrderInternal();
			}
			else {
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
				Stripe.card.createToken(
					{
						cvc: this.creditCardVerificationNumber()
						,exp_month: this.creditCardExpMonth()
						,exp_year: this.creditCardExpYear()
						,number: this.creditCardNumber()
					},
					/**
					 * 2016-03-02
					 * @param {Number} status
					 * @param {Object} response
					 */
					function(status, response) {
						if (200 === status) {
							// 2016-03-02
							// https://stripe.com/docs/custom-form#step-3-sending-the-form-to-your-server
							_this.token = response.id;
							_this.placeOrderInternal();
						}
						else {
							// 2016-03-02
							// https://stripe.com/docs/api#errors
							_this.showErrorMessage(response.error.message);
						}
					}
				);
			}
		}
	}
});});