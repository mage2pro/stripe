<?php
// 2017-01-13
namespace Dfe\Stripe;
final class Card extends \Df\StripeClone\Card {
	/**
	 * 2017-01-13
	 * @override
	 * @see \Df\StripeClone\Card::keyLast4()
	 * @used-by \Df\StripeClone\Card::__toString()
	 * @return string
	 */
	protected function keyLast4() {return 'last4';}

	/**
	 * 2017-01-13
	 * @override
	 * @see \Df\StripeClone\Card::prefixKeyExpiration()
	 * @used-by \Df\StripeClone\Card::expires()
	 * @return string
	 */
	protected function prefixKeyExpiration() {return 'exp';}
}