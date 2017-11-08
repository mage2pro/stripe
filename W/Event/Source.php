<?php
namespace Dfe\Stripe\W\Event;
// 2017-11-08 «A `source.chargeable` event»: https://mage2.pro/t/4889
final class Source extends \Dfe\Stripe\W\Event {
	/**
	 * 2017-11-08
	 * @override
	 * @see \Df\StripeClone\W\Event::ttCurrent()
	 * @used-by \Df\StripeClone\W\Nav::id()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @return string
	 */
	function ttCurrent() {return null;}

	/**
	 * 2017-11-08
	 * @override
	 * @see \Df\StripeClone\W\Event::ttParent()
	 * @used-by \Df\StripeClone\W\Nav::pidAdapt()
	 * @return string
	 */
	function ttParent() {return self::T_3DS;}
}