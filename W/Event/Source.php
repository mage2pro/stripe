<?php
namespace Dfe\Stripe\W\Event;
use Df\Payment\Init\Action as A;
// 2017-11-08 «A `source.chargeable` event»: https://mage2.pro/t/4889
final class Source extends \Dfe\Stripe\W\Event {
	/**
	 * 2017-11-08
	 * Note 1.
	 * Stripe really sends 2 `source.chargeable` events for each payment in the 3D Secure verification scenario:
	 * 1) The first `source.chargeable` event is for an initial reusable source:
	 * "An initial reusable source for a card which requires a 3D Secure verification" https://mage2.pro/t/4893
	 * "A `source.chargeable` event for an initial reusable source for a bank card" https://mage2.pro/t/4889
	 * It is sent before the 3D Secure verification, so I just ignore it.
	 * 2) The second `source.chargeable` event is for a derived single-use 3D Secure source:
	 * "A derived single-use 3D Secure source" https://mage2.pro/t/4894
	 * "A `source.chargeable` event for a derived single-use 3D Secure source" https://mage2.pro/t/4895
	 * It is sent after a successful 3D Secure verification, and I handle it (make a charge).
	 * Note 1.
	 * $this->ro('type') returns:
	 * 		«card» for an initial reusable source
	 * 		«three_d_secure» for a derived single-use 3D Secure source
	 * @override
	 * @see \Df\Payment\W\Event::checkIgnored()
	 * @used-by \Df\Payment\W\Action::execute()
	 * @return false|string
	 */
	function checkIgnored() {return 'card' !== $this->ro('type') ? false : 'source.chargeable [type=card]';}

	/**
	 * 2017-11-10
	 * @override
	 * @see \Df\Payment\W\Event::logTitleSuffix()
	 * @used-by \Df\Payment\W\Handler::log()
	 * @return string|null
	 */
	function logTitleSuffix() {return dftr($this->ro('type'), [
		// 2017-11-10 "An initial reusable source for a card": https://mage2.pro/t/4893
		'card' => 'An initial reusable source for a card'
		// 2017-11-10 "A derived single-use 3D Secure source": https://mage2.pro/t/4894
		,'three_d_secure' => 'A derived single-use 3D Secure source'
	]);}

	/**
	 * 2017-11-08
	 * 2017-11-10
	 * The result is not used by @see \Df\StripeClone\W\Nav::id(),
	 * because that method is overriden by @see \Dfe\Stripe\W\Nav\Source::id()
	 * @override
	 * @see \Df\StripeClone\W\Event::ttCurrent()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @used-by \Dfe\Stripe\W\Event\Source::ttCurrent()
	 * @return string
	 */
	function ttCurrent() {return A::sg($this->m())->preconfiguredToCapture() ? self::T_CAPTURE : self::T_AUTHORIZE;}

	/**
	 * 2017-11-08
	 * @override
	 * @see \Df\StripeClone\W\Event::ttParent()
	 * @used-by \Df\StripeClone\W\Nav::pidAdapt()
	 * @return string
	 */
	function ttParent() {return self::T_3DS;}
}