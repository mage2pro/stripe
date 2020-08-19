<?php
namespace Dfe\Stripe\W\Handler;
# 2017-11-10
final class Source extends \Df\Payment\W\Handler {
	/**
	 * 2017-11-10
	 * @override
	 * @see \Df\Payment\W\Handler::strategyC()
	 * @used-by \Df\Payment\W\Handler::handle()
	 */
	protected function strategyC() {return \Dfe\Stripe\W\Strategy\Charge3DS::class;}
}