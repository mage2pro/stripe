<?php
namespace Dfe\Stripe\W\Handler\Charge;
# 2017-01-04
# 2017-08-16 We get this event when the merchant has just captured a preauthorized payment from his Stripe dashboard.
final class Captured extends \Df\Payment\W\Handler {
	/**
	 * 2017-03-13
	 * @override
	 * @see \Df\Payment\W\Handler::strategyC()
	 * @used-by \Df\Payment\W\Handler::handle()
	 */
	protected function strategyC():string {return \Df\Payment\W\Strategy\CapturePreauthorized::class;}
}