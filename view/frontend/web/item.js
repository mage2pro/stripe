define ([
	'Magento_Payment/js/view/payment/cc-form'
	,'jquery'
	, 'df'
	, 'underscore'
], function(Component, $, df, _) {
	'use strict';
	return Component.extend({
		defaults: {
			active: false
			,clientConfig: {id: 'dfe-stripe'}
			,code: 'dfe_stripe'
			,template: 'Dfe_Stripe/item'
		},
		imports: {onActiveChange: 'active'},
		/**
		 * 2016-03-01
		 * @returns {Array}
	 	 */
		getCardTypes: function() {return _.keys(this.getCcAvailableTypes());},
		/** @returns {String} */
		getCode: function() {return this.code;},
		/**
		 * @returns {exports.initObservable}
		 */
		initObservable: function() {
			this._super();
			return this;
		},
		pay: function() {debugger;}
	});
});
