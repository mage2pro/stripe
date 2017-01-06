<?php
// 2017-01-04
namespace Dfe\Stripe\Webhook\Charge;
class Refunded extends \Dfe\Stripe\Webhook\Charge {
	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\Webhook::currentTransactionType()
	 * @used-by \Df\StripeClone\Webhook::id()
	 * @return string
	 */
	final protected function currentTransactionType() {return 'refund';}

	/**
	 * 2016-12-16
	 * @override
	 * @see \Dfe\Stripe\Webhook\Charge::parentTransactionType()
	 * @used-by \Dfe\Stripe\Webhook\Charge::id()
	 * @return string
	 */
	final protected function parentTransactionType() {return 'capture';}
}