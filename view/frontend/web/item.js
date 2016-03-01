define ([
	'Magento_Payment/js/view/payment/cc-form'
	,'jquery'
	, 'df'
], function(Component, $, df) {
	'use strict';
	return Component.extend({
		defaults: {
			active: false
			,clientConfig: {id: 'dfe-stripe'}
			,code: 'dfe_stripe'
			,template: 'Dfe_Stripe/item'
		},
		imports: {onActiveChange: 'active'},
		/** @returns {String} */
		getCode: function() {return this.code;},
		/**
		 * @returns {exports.initObservable}
		 */
		initObservable: function() {
			this._super();
			return this;
		},
		pay: function() {}
	});
});
