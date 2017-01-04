<?php
// 2017-01-03
namespace Dfe\Stripe;
class Webhook extends \Df\StripeClone\Webhook {
	/**
	 * 2017-01-03
	 * @override
	 * @see \Df\Payment\Webhook::_handle()
	 * @used-by \Df\Payment\Webhook::handle()
	 * @return void
	 */
	protected function _handle() {}

	/**
	 * 2017-01-04
	 * @override
	 * @see \Df\StripeClone\Webhook::roPrefix()
	 * @used-by \Df\StripeClone\Webhook::ro()
	 * @used-by ro()
	 * @return string
	 */
	final protected function roPrefix() {return 'data/object';}
}