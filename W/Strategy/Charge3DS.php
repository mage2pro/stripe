<?php
namespace Dfe\Stripe\W\Strategy;
use Df\Payment\W\Strategy\ConfirmPending;
// 2017-11-10
/** @used-by \Dfe\Stripe\W\Handler\Source */
final class Charge3DS extends \Df\Payment\W\Strategy {
	/**
	 * 2017-11-10
	 * @override
	 * @see \Df\Payment\W\Strategy::_handle()
	 * @used-by \Df\Payment\W\Strategy::::handle()
	 */
	protected function _handle() {
		dfp_webhook_case($this->op(), false);
		$this->delegate(ConfirmPending::class);
	}
}