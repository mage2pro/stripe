<?php
namespace Dfe\Stripe\W\Handler\Source;
/**
 * 2017-11-08 `source.chargeable`
 * Note 1. «Occurs whenever a source transitions to `chargeable`»
 * https://stripe.com/docs/api/php#event_types-source.chargeable
 * Note 2. «A Source object becomes `chargeable` after a customer has authenticated and verified a payment»
 * https://stripe.com/docs/sources/three-d-secure#webhooks
 * Note 3. «A `source.chargeable` event»: https://mage2.pro/t/4889
 * Note 4. Currently, the class behaves the same as @see \Df\PaypalClone\W\Handler
 */
final class Chargeable extends \Df\Payment\W\Handler {
	/**
	 * 2017-11-08
	 * @override
	 * @see \Df\Payment\W\Handler::strategyC()
	 * @used-by \Df\Payment\W\Handler::handle()
	 * @return string
	 */
	protected function strategyC() {return \Df\Payment\W\Strategy\ConfirmPending::class;}
}