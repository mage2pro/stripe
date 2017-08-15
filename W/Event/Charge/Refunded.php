<?php
namespace Dfe\Stripe\W\Event\Charge;
// 2017-03-15
final class Refunded extends \Dfe\Stripe\W\Event {
	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\W\Event::ttCurrent()
	 * @used-by \Df\StripeClone\W\Event::id()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @return string
	 */
	function ttCurrent() {return self::T_REFUND;}
	
	/**
	 * 2016-12-16
	 * @override
	 * @see \Df\StripeClone\W\Event::ttParent()
	 * @used-by \Df\StripeClone\W\Nav::pidAdapt()
	 * @return string
	 */
	function ttParent() {return self::T_CAPTURE;}
}