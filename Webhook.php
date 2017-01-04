<?php
// 2017-01-03
namespace Dfe\Stripe;
class Webhook extends \Df\StripeClone\Webhook {
	/**
	 * 2016-12-26
	 * @override
	 * @see \Df\Payment\Webhook::config()
	 * @used-by \Df\Payment\Webhook::configCached()
	 * @return array(string => mixed)
	 */
	final protected function config() {return [self::$typeKey => 'type'];}

	/**
	 * 2017-01-03
	 * @override
	 * @see \Df\Payment\Webhook::_handle()
	 * @used-by \Df\Payment\Webhook::handle()
	 * @return void
	 */
	protected function _handle() {}
}