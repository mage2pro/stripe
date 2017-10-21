/**
 * 2016-03-01
 * 2017-10-12 «Stripe.js v2 Reference»: https://stripe.com/docs/stripe.js/v2
 * 2017-10-16
 * I am starting to move the code to the «Elements» technology:
 * *) «Stripe Elements Quickstart»: https://stripe.com/docs/elements
 * *) «Stripe.js Reference»: https://stripe.com/docs/stripe.js
 * *) «Creating a custom payment form with Stripe.js is deprecated»:
 * https://github.com/mage2pro/stripe/issues/3
 */
define([
	'df', 'Df_StripeClone/main', 'jquery', 'Magento_Checkout/js/model/quote'
   /**
	* 2017-10-16
	* «Including Stripe.js»: https://stripe.com/docs/stripe.js#including-stripejs
	* «To best leverage Stripe’s advanced fraud functionality,
	* include this script on every page on your site, not just the checkout page.
	* This allows Stripe to detect anomalous behavior
	* that may be indicative of fraud as users browse your website.»
	* https://github.com/mage2pro/stripe/issues/33
	* I have implemented it, @see \Dfe\Stripe\Block\Js::_toHtml():
	*	final protected function _toHtml() {return !dfps($this)->enable() ? '' : df_js(
	*		null, 'https://js.stripe.com/v2/'
	*	);}
	* https://github.com/mage2pro/stripe/blob/2.1.1/Block/Js.php#L39-L41
	* But I need to require Stripe.js here too,
	* because I need to encure that the script is loaded before Dfe_Stripe/main.js execution.
	*/
	,'https://js.stripe.com/v3/'
], function(df, parent, $, quote) {'use strict';
/** 2017-09-06 @uses Class::extend() https://github.com/magento/magento2/blob/2.2.0-rc2.3/app/code/Magento/Ui/view/base/web/js/lib/core/class.js#L106-L140 */
return parent.extend({
	defaults: {df: {card: {new: {
		/**
		 * 2017-10-16
		 * @override
		 * @see Df_Payment/card
		 */
		fields: 'Dfe_Stripe/card/fields'
	}}}},
	/**
	 * 2017-10-12
	 * r looks like:
	 *	{
	 *		card: {
	 *			address_city: "Palo Alto",
	 *			address_country: "US",
	 *			address_line1: "12 Main Street",
	 *			address_line2: "Apt 42",
	 *			address_state: "CA",
	 *			address_zip: "94301",
	 *			brand: "Visa",
	 *			country: "US",
	 *			exp_month: 2,
	 *			exp_year: 2018,
	 *			funding: "credit",
	 *			last4: "4242",
	 *			name: null,
	 *			object: "card"
	 *		},
	 *		created: 1507803936,
	 *		id: "tok_8DPg4qjJ20F1aM",
	 *		livemode: true,
	 *		object: "token",
	 *		type: "card",
	 *		used: false
	 *	}
	 * https://stripe.com/docs/stripe.js/v2
	 * 2017-10-17
	 * Note 1. «The token object»: https://stripe.com/docs/api#token_object
	 * Note 2. `brand`:
	 * «Card brand. Can be Visa, American Express, MasterCard, Discover, JCB, Diners Club, or Unknown.»
	 * https://stripe.com/docs/api#token_object-card-brand
	 * @override
	 * @see Df_StripeClone/main::dfDataFromTokenResp()
	 * @used-by Df_StripeClone/main::dfData()
	 * @param {Object} r
	 * @returns {Object}
	 */
	dfDataFromTokenResp: function(r) {return {cardType: r.token.card.brand};},
	/**
	 * 2017-10-17
	 * @override
	 * @see Df_Payment/card::dfFormCssClasses()
	 * https://github.com/mage2pro/core/blob/3.2.6/Payment/view/frontend/web/card.js#L113-L122
	 * @used-by Df_Payment/mixin::dfFormCssClassesS()
	 * https://github.com/mage2pro/core/blob/2.0.25/Payment/view/frontend/web/mixin.js?ts=4#L171
	 * @returns {String[]}
	 */
	dfFormCssClasses: function() {return this._super().concat([
		this.singleLineMode() ? 'df-singleLineMode' : 'df-multiLineMode'
	]);},
 	/**
	 * 2017-10-16
	 * Magento <= 2.1.0 calls an `afterRender` handler outside of the `this` context.
	 * It passes `this` to an `afterRender` handler as the second argument:
	 * https://github.com/magento/magento2/blob/2.0.9/app/code/Magento/Ui/view/base/web/js/lib/ko/bind/after-render.js#L19
	 * Magento >= 2.1.0 calls an `afterRender` handler within the `this` context:
	 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Ui/view/base/web/js/lib/knockout/bindings/after-render.js#L20
	 * @used-by Dfe_Stripe/fields.html
	 * @param {HTMLElement} e
	 * @param {Object} _this
	 */
	dfOnRender: function(e, _this) {$.proxy(function(e) {
		/** @type {jQuery} HTMLDivElement */ var $e = $(e);
		/** @type {String} */ var type = $e.data('type');
		/** @type {Object} */ this.stripe = this.stripe || Stripe(this.publicKey());
		/**
		 * 2017-10-17
		 * We need a single instance of Elements,
		 * otherwise Stripe will think the elements are belogs to separate forms.
		 * https://stackoverflow.com/a/42963215
		 * @type {Object}
		 */
		this.stripeElements = this.stripeElements || this.stripe.elements();
		/**
		 * 2017-10-16
		 * https://stripe.com/docs/stripe.js#stripe-function
		 * https://stripe.com/docs/stripe.js#stripe-elements
		 * «A flexible single-line input that collects all necessary card details.»
		 * https://stripe.com/docs/stripe.js#element-types
		 * `Element` options: https://stripe.com/docs/stripe.js#element-options
		 * @type {Object}
		 */
		var lElement = this.stripeElements.create(type, {
			// 2017-08-25 «Hides any icons in the Element. Default is false.»
			hideIcon: false
			// 2017-08-25
			// «Hide the postal code field (if applicable to the Element you're creating).
			// Default is false.
			// If you are already collecting a billing ZIP or postal code on the checkout page,
			// you should set this to true.»
			,hidePostalCode: true
			// 2017-08-25 «Appearance of the icons in the Element. Either 'solid' or 'default'.»
			,iconStyle: 'solid'
			/**
			 * 2017-08-25
			 * Note 1: «Customize the placeholder text.
			 * This is only available for the cardNumber, cardExpiry, cardCvc, and postalCode Elements.»
			 * Note 2: If the `placeholder` key is present for a `card` element (even with an empty value),
			 * then Stripe warns in the browser's console:
			 * «This Element (card) does not support custom placeholders.»
			 */
			//,placeholder: ''
			/**
			 * 2017-08-25
			 * «Customize appearance using CSS properties.
			 * Style is specified as an object for any of the following variants:
			 * 	*) `base`: base style—all other variants inherit from this style
			 *	*) `complete`: applied when the Element has valid input
			 *	*) `empty`: applied when the Element has no user input
			 *	*) `invalid`: applied when the Element has invalid input
			 * For each of the above, the following properties can be customized:
			 * 		`color`
			 * 		`fontFamily`
			 * 		`fontSize`
			 * 		`fontSmoothing`
			 * 		`fontStyle`
			 * 		`fontVariant`
			 * 		`iconColor`
			 * 		`lineHeight`
			 * 		`letterSpacing`
			 * 		`textAlign`: Avaliable for the cardNumber, cardExpiry, cardCvc, and postalCode Elements.
			 * 		`textDecoration`
			 * 		`textShadow`
			 * 		`textTransform`
			 * The following pseudo-classes and pseudo-elements can also be styled with the above properties,
			 * as a nested object inside the variant:
			 * 		:hover
			 * 		:focus
			 * 		::placeholder
			 * 		::selection
			 * 		:-webkit-autofill
			 * »
			 */
			,style: {base: {
				'::placeholder': {color: 'rgb(194, 194, 194)'}
				,color: 'black'
				,fontFamily: "'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif"
				,fontSize: '14px'
				,iconColor: '#1979c3'
				,lineHeight: '1.42857143'
			}}
			/**
			 * 2017-08-25
			 * «A pre-filled value (for single-field inputs) or set of values (for multi-field inputs)
			 * to include in the input (e.g., {postalCode: '94110'}).
			 * Note that sensitive card information (card number, CVC, and expiration date) cannot be pre-filled.»
			 */
			,value: {}
		});
		// 2017-08-25
		// «mount() accepts either a CSS Selector or a DOM element.»
		// https://stripe.com/docs/stripe.js#element-mount
		lElement.mount(e);
		/**
		 * 2017-10-21 «Stripe.js Reference» → «element.on(event, handler)».
		 * https://stripe.com/docs/stripe.js#element-on
		 */
		lElement.on('change', $.proxy(function(event) {
			/**
			 * 2017-10-21
			 * «The current validation error, if any.
			 * Comprised of `message`, `code`, and `type` set to `validation_error`.»
			 * https://stripe.com/docs/stripe.js#element-on
			 */
			if (event.error) {
				this.showErrorMessage(event.error.message);
			}
			else {
				this.messageContainer.clear();
				/**
				 * 2017-10-21
				 * Note 1.
				 * «Applies to the `card` and `cardNumber` Elements only.
				 * Contains the card brand (e.g., `visa` or `amex`) of the card number being entered.»
				 * https://stripe.com/docs/stripe.js#element-on
				 *
				 * Note 2.
				 * The `this.selectedCardType` property is used not only for decoration
				 * (to show the selected card brang logotype),
				 * but also by @see Df_Payment/card::validate():
				 *	var r = !this.isNewCardChosen() || !!this.selectedCardType();
				 *	if (!r) {
				 *		this.showErrorMessage(
				 *			'It looks like you have entered an incorrect bank card number.'
				 *		);
				 *	}
				 * https://github.com/mage2pro/core/blob/3.2.14/Payment/view/frontend/web/card.js#L287-L299
				 * So it is vital to initialize it, otherwise we will get the failure:
				 * «It looks like you have entered an incorrect bank card number»
				 * https://github.com/mage2pro/stripe/issues/44
				 *
				 * Note 3.
				 * The `event.brand` property is present
				 * only on the `card` and `cardNumber` Elements edition.
				 * It is not present on other elements edition (e.g. `cardCvc`).
				 *
				 * Note 4.
				 * The Stripe documentation does not enumerate
				 * all the `event.brand` possitble values.
				 * I have found them experimentally by entering the test card numbers of all the brands:
				 * https://stripe.com/docs/testing#cards
				 */
				if (event.brand) {
					this.selectedCardType(df.tr(event.brand, {
						amex: 'AE'
						,discover: 'DI'
						,diners: 'DN'
						,jsb: 'JCB'
						,mastercard: 'MC'
						,visa: 'VI'
					}));
				}
			}
		}, this));
		if (-1 !== ['card', 'cardNumber'].indexOf(type)) {
			this.lCard = lElement;
		}
	}, _this, e)();},
	/**
	 * 2016-03-01
	 * 2016-03-08
	 * Раньше реализация была такой:
	 * return _.keys(this.getCcAvailableTypes())
	 *
	 * https://web.archive.org/web/20160321062153/https://support.stripe.com/questions/which-cards-and-payment-types-can-i-accept-with-stripe
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
	 * 2017-02-05 The bank card network codes: https://mage2.pro/t/2647
	 *
	 * 2017-10-12
	 * Note 1. «JCB, Discover, and Diners Club cards can only be charged in USD»:
	 * https://github.com/mage2pro/stripe/issues/28
	 * Note 2. «Can a non-USA merchant accept the JCB, Discover, and Diners Club bank cards?»
	 * https://mage2.pro/t/4670
	 * @returns {String[]}
	 */
	getCardTypes: function() {return(
		['VI', 'MC', 'AE'].concat(!this.config('isUS') ? [] : ['JCB', 'DI', 'DN'])
	);},
	/**
	 * 2017-10-17
	 * @returns {Boolean}
	 */
	singleLineMode: function() {return this.config('singleLineMode');},
    /**
	 * 2017-02-16
	 * 2017-10-17 https://stripe.com/docs/stripe.js#stripe-create-token
	 * @override
	 * @see Df_StripeClone/main::tokenCheckStatus()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L8-L15
	 * @used-by Df_StripeClone/main::placeOrder()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L75
	 * @param {Object} r
	 * @returns {Boolean}
	 */
	tokenCheckStatus: function(r) {return !r.error;},
    /**
	 * 2017-02-16
	 * 2017-08-25
	 * https://stripe.com/docs/stripe.js#stripe-create-token
	 * «stripe.createToken returns a Promise which resolves with a result object.»
	 * https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Promise
	 * @override
	 * @see https://github.com/mage2pro/core/blob/2.0.11/StripeClone/view/frontend/web/main.js?ts=4#L21-L29
	 * @used-by Df_StripeClone/main::placeOrder()
	 * https://github.com/mage2pro/core/blob/2.7.9/StripeClone/view/frontend/web/main.js?ts=4#L73
	 * @param {Object} params
	 * @param {Function} callback
	 */
	tokenCreate: function(params, callback) {
		this.stripe.createToken(this.lCard, params).then(function(r) {callback(r, r);})
	;},
    /**
	 * 2017-02-16 https://stripe.com/docs/api#errors
	 * 2017-10-17 https://stripe.com/docs/stripe.js#stripe-create-token
	 * @override
	 * @see https://github.com/mage2pro/core/blob/2.0.11/StripeClone/view/frontend/web/main.js?ts=4#L31-L39
	 * @used-by placeOrder()
	 * @param {Object} r
	 * @returns {String}
	 */
	tokenErrorMessage: function(r) {return r.error.message;},
    /**
	 * 2017-02-16
	 * 2017-10-17
	 * https://stripe.com/docs/stripe.js#stripe-create-token
	 * «The token object»: https://stripe.com/docs/api#token_object
	 * @override
	 * @see https://github.com/mage2pro/core/blob/2.0.11/StripeClone/view/frontend/web/main.js?ts=4#L41-L48
	 * @used-by placeOrder()
	 * @param {Object} r
	 * @returns {String}
	 */
	tokenFromResponse: function(r) {return r.token.id;},
    /**
	 * 2017-02-16
	 * 2017-08-31
	 * Note 1. https://stripe.com/docs/stripe.js/v2#card-createToken
	 * Note 2. `I want to pass the customer's billing address to the createToken() Stripe.js method`
	 * https://mage2.pro/t/4412
	 * Note 3. `Use rules to automatically block payments or place them in review`:
	 * https://stripe.com/docs/disputes/prevention#using-rules
	 * 2017-10-17 https://stripe.com/docs/stripe.js#stripe-create-token
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
			,name: this.cardholder() // 2017-08-31 «cardholder name», optional.
		};
	}
});});