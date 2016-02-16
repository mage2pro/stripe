define ([
	'Magento_Checkout/js/view/payment/default', 'Magento_Checkout/js/model/quote'
], function (Component, quote) {
	'use strict';
	return Component.extend ({defaults: {template: 'Dfe_Stripe/item'}});
});
