<?php
namespace Dfe\Stripe\W\Handler\Source;
/**
 * 2017-11-08
 * «A Source object expired and cannot be used to create a charge.»
 * https://stripe.com/docs/sources/three-d-secure#webhooks
 */
final class Canceled extends \Df\Payment\W\Handler {
	/**
	 * 2017-11-08
	 * @override
	 * @see \Df\Payment\W\Handler::strategyC()
	 * @used-by \Df\Payment\W\Handler::handle()
	 * @return string
	 */
	protected function strategyC() {return '';}
}