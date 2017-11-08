<?php
namespace Dfe\Stripe\W\Handler\Source;
/**
 * 2017-11-08 `source.failed`
 * Note 1. «Occurs whenever a source fails»
 * https://stripe.com/docs/api/php#event_types-source.failed
 * Note 2.
 * «A Source object failed to become `chargeable`
 * as your customer declined or failed to authenticate the payment»
 * https://stripe.com/docs/sources/three-d-secure#webhooks
 * Note 3. Currently, the class behaves the same as @see \Df\PaypalClone\W\Handler
 */
final class Failed extends \Df\Payment\W\Handler {
	/**
	 * 2017-11-08
	 * @override
	 * @see \Df\Payment\W\Handler::strategyC()
	 * @used-by \Df\Payment\W\Handler::handle()
	 * @return string
	 */
	protected function strategyC() {return \Df\Payment\W\Strategy\ConfirmPending::class;}
}