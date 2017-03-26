<?php
// 2017-01-04
namespace Dfe\Stripe\W\Handler\Charge;
use Df\StripeClone\W\Strategy\CapturePreauthorized as Strategy;
final class Captured extends \Df\StripeClone\W\Handler {
	/**
	 * 2017-03-13
	 * @override
	 * @see \Df\StripeClone\W\Handler::strategyC()
	 * @used-by \Df\StripeClone\W\Handler::_handle()
	 * @return string
	 */
	protected function strategyC() {return Strategy::class;}	
}