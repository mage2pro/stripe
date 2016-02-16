define([
	'uiComponent', 'Magento_Checkout/js/model/payment/renderer-list'
], function(Component, rendererList) {
	'use strict';
	rendererList.push({type: 'dfe_stripe', component: 'Dfe_Stripe/item'});
	return Component.extend ({});
});
