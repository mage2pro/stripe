<?php
// 2017-01-04
namespace Dfe\Stripe\W\Handler\Charge;
use Df\Payment\W\Strategy\CapturePreauthorized as Strategy;
final class Captured extends \Df\StripeClone\W\Handler {
	/**
	 * 2017-03-13
	 * @override
	 * @see \Df\Payment\W\Handler::strategyC()
	 * @used-by \Df\Payment\W\Handler::handle()
	 * @return string
	 */
	protected function strategyC() {return Strategy::class;}	
}