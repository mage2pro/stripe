<?php
// 2017-01-04
namespace Dfe\Stripe\Webhook;
abstract class Charge extends \Dfe\Stripe\Webhook {
	/**
	 * 2016-12-16
	 * @used-by \Dfe\Stripe\Webhook\Charge::id()
	 * @see \Dfe\Stripe\Webhook\Charge\Captured::parentTransactionType()
	 * @see \Dfe\Stripe\Webhook\Charge\Refunded::parentTransactionType()
	 * @return string
	 */
	abstract protected function parentTransactionType();
}