<?php
namespace Dfe\Stripe\W\Handler\Charge;
use Df\Payment\W\Strategy\CapturePreauthorized as Strategy;
// 2017-01-04
final class Captured extends \Df\Payment\W\Handler {
	/**
	 * 2017-03-13
	 * @override
	 * @see \Df\Payment\W\Handler::strategyC()
	 * @used-by \Df\Payment\W\Handler::handle()
	 * @return string
	 */
	protected function strategyC() {return Strategy::class;}	
}