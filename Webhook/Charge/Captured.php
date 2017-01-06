<?php
// 2017-01-04
namespace Dfe\Stripe\Webhook\Charge;
class Captured extends \Dfe\Stripe\Webhook\Charge {
	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\Webhook::currentTransactionType()
	 * @used-by \Df\StripeClone\Webhook::id()
	 * @return string
	 */
	final protected function currentTransactionType() {return 'capture';}

	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\Webhook::parentTransactionType()
	 * @used-by \Df\StripeClone\Webhook::adaptParentId()
	 * @return string
	 */
	final protected function parentTransactionType() {return 'authorize';}
}