// 2019-02-22
var config = {config: {mixins: {
	// 2019-02-22
	// It would solve compatibility problems with third-party checkout modules.
	// Similar to: https://github.com/mage2pro/vantiv/issues/3
	'Magento_Checkout/js/view/payment/list': {'Dfe_Stripe/payment-list': true}
}}};