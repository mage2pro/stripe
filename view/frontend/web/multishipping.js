// 2017-08-25 «Step 1: Set up Stripe Elements»: https://stripe.com/docs/elements#setup
// 2017-08-26 @todo 'Df_Intl/t' does not work here...
define([
	'df', 'df-lodash', 'jquery', 'mage/translate', 'rjsResolver', 'https://js.stripe.com/v3/'
], function(df, _, $, $t, resolver) {return (
	/**
	 * 2017-08-25
	 * @param {Object} config
	 * @param {String} config.publicKey
	 * @param {Object} config.sourceData
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
		* @param {Boolean} isNew
	    * @param {String=} cardType
	    */
		var setResult = function(token, isNew, cardType) {
			var addHiddenInput = function(n, v) {
				$element.append($('<input>').attr({name: 'payment[' + n + ']', type: 'hidden', value: v}));
			};
		   /**
		    * 2017-10-22
		    * I add the «new_» prefix to a new source ID to distinguish it from the previously used sources.
		    * @see \Dfe\Stripe\Facade\Token::trimmed()
		    */
			addHiddenInput('token', (isNew ? 'new_' : '') + token);
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
						setResult(o, false);
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
		// 2017-10-21 «Stripe.js Reference» → «element.on(event, handler)».
		// https://stripe.com/docs/stripe.js#element-on
		lCard.on('change', function(event) {
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
			 * 2017-08-25
			 * https://stripe.com/docs/stripe.js#stripe-create-token
			 * «stripe.createToken returns a Promise which resolves with a result object.»
			 * https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Promise
			 * 2017-10-20
			 * Note 1.
			 * `Create a Source object instead of a token on the client side:
			 * it will allow us to implement additional Stripe's payment options in future
			 * (3D Secure, local European and Chinese payment options, etc.)`:
			 * https://github.com/mage2pro/stripe/issues/40
			 * Note 2.
			 * «Use `stripe.createSource` to convert payment information collected by Elements
			 * into a Source object that you safely pass to your server to use in an API call.»
			 * https://stripe.com/docs/stripe.js#stripe-create-source
			 */
			stripe.createSource(lCard, _.assign(config.sourceData, {
			/**
			 * 2019-03-25
			 * I have added the `df.clean()` call by analogy with the single-shipping code:
			 * https://github.com/mage2pro/stripe/blob/2.7.0/view/frontend/web/main.js#L471-L480
			 * My explanation for the single-shipping code:
			 * "An empty value for `name` leads to the failure:
			 * «You passed an empty string for 'owner[name]'.
			 * We assume empty values are an attempt to unset a parameter;
			 * however 'owner[name]' cannot be unset.
			 * You should remove 'owner[name]' from your request or supply a non-empty value.»
			 * https://mage2.pro/t/5011
			 * To evade such failure, I have added df.clean()."
		 	 */
				owner: df.clean({name: $('.cardholder input').val()})
			}))
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
						*
						* 2017-10-21
						* Note 1. «Stripe API Reference» → «Sources» → «The source object»:
						* https://stripe.com/docs/api#source_object
						* Note 2. A response to `stripe.createSource`: https://mage2.pro/t/4728
						*/
						setResult(r.source.id, true, r.source.card.brand);
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