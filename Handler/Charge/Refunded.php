<?php
namespace Dfe\Stripe\Handler\Charge;
use Dfe\Stripe\Handler\Charge;
// 2016-03-25
// https://stripe.com/docs/api#event_types-charge.refunded
class Refunded extends Charge {
	/**
	 * 2016-03-25
	 * @override
	 * @see \Dfe\Stripe\Handler::_process()
	 * @used-by \Dfe\Stripe\Handler::process()
	 * @return void
	 */
	protected function process() {
		/** @var array(string => string) $object */
		//$object = dfa_deep($request, 'data/object');
		/** @var string $charge */
		//$charge = $object['id'];
		/** @var string $amount */
		//$amount = $object['amount'];
		/** @var string $charge */
		//$amount_refunded = $object['amount_refunded'];
	}
}