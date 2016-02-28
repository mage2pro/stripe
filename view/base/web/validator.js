define([
    'underscore', 'df'
], function (_, df) {
	'use strict';
	return {
		config: {},
		/**
	     * @returns {*|exports.defaults.availableCardTypes|{}}
	     */
		getAvailableCardTypes: function() {return this.config.availableCardTypes;},
		/**
		 * @param {Object} config
		 */
		setConfig: function (config) {this.config = config;}
	}
});