// 2017-08-25 «Step 1: Set up Stripe Elements»: https://stripe.com/docs/elements#setup
// 2017-08-26 @todo 'Df_Intl/t' does not work here...
define([
	'df-lodash', 'jquery', 'mage/translate', 'rjsResolver', 'https://js.stripe.com/v3/'
], function(_, $, $t, resolver) {return (
	/**
	 * 2017-08-25
	 * @param {Object} config
	 * @param {Object} config.ba
	 * @param {String} config.publicKey
	 * @param {HTMLAnchorElement} element
	 * @returns void
	 */
	function(config, element) {
		/**
		 * 2017-10-16
		 * @see \Dfe\Stripe\Block\Multishipping::_toHtml()
		 * https://github.com/mage2pro/stripe/blob/2.1.0/Block/Multishipping.php#L38-L45
		 * @type {jQuery} HTMLDivElement
		 */
		var $element = $(element);
		// 2017-08-26 It is the «Go to Review Your Order» button.
		/** @type {HTMLButtonElement} */
		var eContinue = document.getElementById('payment-continue');
		var cards = config['cards'];
		//noinspection JSJQueryEfficiency
		var $methods = $('#payment-methods input[type=radio][name=payment\\[method\\]]');
		/** 2017-08-27 @returns {Boolean} */
		var isOurMethodSelected = function() {return 'dfe_stripe' === $methods.filter(':checked').val();};
		/** 2017-08-27 @returns {String} */
		var optionSelected = function() {return !cards.length ? 'new' :
			$('input[type=radio][name=option]:checked', $element).val()
		;};
		var updateContinue = function() {
			eContinue.disabled = isOurMethodSelected() && (!cards.length || 'new' === optionSelected());
		};
	  /**
	   * 2017-08-28
	   * Note 1.
	   * The name should have the «payment» namespace:
	   * @used-by \Magento\Multishipping\Controller\Checkout\Overview::execute():
	   * 	$payment = $this->getRequest()->getPost('payment', []);
	   * https://github.com/magento/magento2/blob/2.2.0-rc2.2/app/code/Magento/Multishipping/Controller/Checkout/Overview.php#L27
	   * Note 2.
	   * The param should be named «token»:
	   * @see \Df\Payment\Token::KEY
	   * 	const KEY = 'token';
	   * https://github.com/mage2pro/core/blob/2.10.46/Payment/Token.php#L36
	   * @param {String} token
	   * @param {String=} cardType
	   */
		var setResult = function(token, cardType) {
			var addHiddenInput = function(n, v) {
				$element.append($('<input>').attr({name: 'payment[' + n + ']', type: 'hidden', value: v}));
			};
			addHiddenInput('token', token);
		   /**
		    * 2017-10-19
			* `Pass the brand of ther used bank card from the payment form to the Magento server part
			* in the multi-shipping scenario (in the same way as it is happen in the single-shipping scenario)`:
			* https://github.com/mage2pro/stripe/issues/35
			* The single-shipping scenario's implementation:
			* https://github.com/mage2pro/stripe/blob/2.2.0/view/frontend/web/main.js#L71-L80
		    */
			if (cardType) {
				addHiddenInput('cardType', cardType);
			}
		};
		$methods.change(function(){
			updateContinue();
			// 2017-08-26
			// Unable to use this.value instead of $methods.filter(':checked').val() here,
			// because below we will fire the `change` event manually:
			//	$methods.trigger('change');
			$element.toggle(isOurMethodSelected());
		});
		(function() {
			if (cards.length) {
				var buildOption = function(id, label) {
					var $r = $('<div>').addClass('field choice df-choice');
					$r.append($('<input>').attr({
						id: id, 'class': 'radio', name: 'option', type: 'radio', value: id
					}));
					$r.append($('<label>').attr('for', id).append($('<span>').html($t(label))));
					return $r;
				};
				var $optionsC = $('<div>');
				_.each(cards, function(card) {
					$optionsC.append(buildOption(card.id, card.label));
				});
				$optionsC.append(buildOption('new', 'Another card'));
				$element.prepend($optionsC);
				var $new = $('.df-card-new');
				var $options = $('input[type=radio][name=option]', $optionsC);
				// 2017-08-26 «How to use radio on change event?»: https://stackoverflow.com/a/13152970
				$options.change(function() {
					var isNew = 'new' == this.value;
					$new.toggle(isNew);
					if (isOurMethodSelected()) {
						eContinue.disabled = isNew;
					}
				});
				// 2017-08-26 $options.first().prop('checked', true); does not fire the `change` event.
				$options.first().click();
			}
			/**
			 * 2017-08-28
			 * It will be executed on the «Go to Review Your Order» button click before the form submission.
			 * The form is submitted by the Magento_Multishipping/js/payment::_submitHandler() method:
			 *		_submitHandler: function (e) {
			 *			e.preventDefault();
			 *			if (this._validatePaymentMethod()) {
			 *				this.element.submit();
			 *			}
			 *		}
			 * https://github.com/magento/magento2/blob/2.2.0-rc2.2/app/code/Magento/Multishipping/view/frontend/web/js/payment.js#L125-L136
			 * `How is the «Go to Review Your Order» button implemented
			 * on the «multishipping/checkout/billing» page?` https://mage2.pro/t/4408
			 */
			$('#multishipping-billing-form').submit(function() {
				if (isOurMethodSelected()) {
					/** @type {String} */ var o = optionSelected();
					if ('new' !== o) {
						setResult(o);
					}
				}
			});
		})();
		$methods.trigger('change');
		/** @type {Object} */ var stripe = Stripe(config.publicKey);
		/**
		 * 2017-08-25
		 * «A flexible single-line input that collects all necessary card details.»
		 * https://stripe.com/docs/stripe.js#element-types
		 * `Element` options: https://stripe.com/docs/stripe.js#element-options
		 * 2017-10-16
		 * https://stripe.com/docs/stripe.js#stripe-function
		 * https://stripe.com/docs/stripe.js#stripe-elements
		 * @type {Object}
		 */
		var lCard = stripe.elements().create('card', {
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
		var $message = $('.message', element);
		lCard.addEventListener('change', function(event) {
			$message.html(!event.error ? '' : event.error.message);
			$message.toggle(!!event.error);
		});
		// 2017-08-25
		// «mount() accepts either a CSS Selector or a DOM element.»
		// https://stripe.com/docs/stripe.js#element-mount
		lCard.mount($('.df-stripe-input', element).get(0));
		$('button', element).click(function(ev) {
			ev.preventDefault();
			var $c = $('.box-billing-method');
			$c.trigger('processStart');
			/**
			 * 2017-10-18
			 * Note 1.
			 * An address looks like:
			 *	{
			 *		<...>
			 *		"city": "Paris",
			 *		<...>
			 *		"country_id": "FR",
			 *		<...>
			 *		"postcode": "75008",
			 *		<...>
			 *		"region": "Paris",
			 *		<...>
			 *		"street": "78B Avenue Marceau",
			 *		<...>
			 *	}
			 * @param {Object} a
			 * @param {String=} a.city	«Rio de Janeiro»
			 * @param {String=} a.country_id	«BR»
			 * @param {String=} a.postcode	«22630-010»
			 * @param {String=} a.region	«Rio de Janeiro»
			 * @param {String=} a.street	«["Av. Lúcio Costa, 3150 - Barra da Tijuca"]»
			 *
			 * Note 2.
			 * `Pass the customer's billing address to the createToken() Stripe.js method
			 * in the multi-shipping scenario
			 * (in the same way as it is happen in the single-shipping scenario)`:
			 * https://github.com/mage2pro/stripe/issues/34
			 * 
			 * Note 3.
			 * `The payment form in the frontend multishipping scenario
			 * does not ask a customer for the cardholder name
			 * even if the «Require the cardholder's name?» option is enabled`:
			 * https://github.com/mage2pro/stripe/issues/14
			 */
			var a = config.ba;
			// 2017-08-25
			// https://stripe.com/docs/stripe.js#stripe-create-token
			// «stripe.createToken returns a Promise which resolves with a result object.»
			// https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Promise
			stripe.createToken(lCard, {
				address_city: a.city // 2017-08-31 «billing address city», optional.
				,address_country: a.country_id  // 2017-08-31 «billing address country», optional.
				,address_line1: a.street  // 2017-08-31 «billing address line 1», optional.
				,address_line2: ''  // 2017-08-31 «billing address line 2», optional.
				,address_state: a.region // 2017-08-31 «billing address state», optional.
				,address_zip: a.postcode // 2017-08-31 «billing ZIP code as a string (e.g., "94301")», optional.
				,name: $('.cardholder input').val() // 2017-08-31 «cardholder name», optional.
			})
				.then(function(r) {
					if (r.error) {
						$message.html(r.error.message).show();
					}
					else {
					   /**
						* 2017-10-19
						* Note 1.
						* `Pass the brand of ther used bank card
						* from the payment form to the Magento server part
						* in the multi-shipping scenario
						* (in the same way as it is happen in the single-shipping scenario)`:
						* https://github.com/mage2pro/stripe/issues/35
						* The single-shipping scenario's implementation:
						* https://github.com/mage2pro/stripe/blob/2.2.0/view/frontend/web/main.js#L71-L80
						* Note 2. «The token object»: https://stripe.com/docs/api#token_object
						* Note 3. `brand`:
						* «Card brand.
						* Can be Visa, American Express, MasterCard, Discover, JCB, Diners Club, or Unknown.
						* https://stripe.com/docs/api#token_object-card-brand
						*/
						setResult(r.token.id, r.token.card.brand);
						eContinue.disabled = false;
						$(eContinue).click();
					}
				})
				// 2017-08-25
				// «What is the ES6 Promise equivalent of jQuery Deferred's 'always`?»
				// https://stackoverflow.com/a/32882576
				.then(function() {resolver($c.trigger.bind($c, 'processStop'));})
			;
		});
	});
});