<?php
// 2017-01-04
namespace Dfe\Stripe\Webhook\Charge;
class Refunded extends \Dfe\Stripe\Webhook\Charge {
	/**
	 * 2016-12-16
	 * @override
	 * @see \Dfe\Stripe\Webhook\Charge::parentTransactionType()
	 * @used-by \Dfe\Stripe\Webhook\Charge::id()
	 * @return string
	 */
	final protected function parentTransactionType() {return 'authorize';}
}