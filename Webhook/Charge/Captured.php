<?php
// 2017-01-04
namespace Dfe\Stripe\Webhook\Charge;
use Dfe\Stripe\Method as M;
final class Captured extends \Dfe\Stripe\Webhook\Charge {
	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\Webhook::currentTransactionType()
	 * @used-by \Df\StripeClone\Webhook::id()
	 * @used-by \Df\StripeClone\WebhookStrategy::currentTransactionType()
	 * @return string
	 */
	final public function currentTransactionType() {return M::T_CAPTURE;}

	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\Webhook::parentTransactionType()
	 * @used-by \Df\StripeClone\Webhook::adaptParentId()
	 * @return string
	 */
	final protected function parentTransactionType() {return M::T_AUTHORIZE;}
}