// 2017-08-25
// «Step 1: Set up Stripe Elements»: https://stripe.com/docs/elements#setup
define(['jquery', 'https://js.stripe.com/v3/'], function($) {return (
	/**
	 * 2017-08-25
	 * @param {Object} config
	 * @param {String} config.key
	 * @param {HTMLAnchorElement} element
	 * @returns void
	 */
	function(config, element) {
		var stripe = Stripe(config.key);
		var elements = stripe.elements();
		/**
		 * 2017-08-25
		 * «A flexible single-line input that collects all necessary card details.»
		 * https://stripe.com/docs/stripe.js#element-types
		 * `Element` options: https://stripe.com/docs/stripe.js#element-options
		 * @type {Object}
		 */
		var card = elements.create('card', {
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
				'::placeholder': {
					color: 'rgb(194, 194, 194)'
				}
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
		card.addEventListener('change', function(event) {
			$message.html(!event.error ? '' : event.error.message);
			$message.toggle(!!event.error);
		});
		/**
		 * 2017-08-25
		 * «mount() accepts either a CSS Selector or a DOM element.»
		 * https://stripe.com/docs/stripe.js#element-mount
		 */
		card.mount($('.inputs', element).get(0));
		$('button', element).click(function(ev) {
			ev.preventDefault();
			stripe.createToken(card).then(function(r) {
				if (r.error) {
					$message.html(r.error.message);
				}
				else {
					$message.html(r.token.id);
				}
				$message.show();
			});
		});
	});
});