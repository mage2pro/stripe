<?php
namespace Dfe\Stripe;
// 2016-08-20
final class ResponseRecord extends \Df\StripeClone\ResponseRecord {
	/**
	 * 2017-01-13
	 * Returns the path to the bank card information in the payment system response.
	 * @override
	 * @see \Df\StripeClone\ResponseRecord::keyCard()
	 * @used-by \Df\StripeClone\ResponseRecord::card()
	 * @return string
	 */
	protected function keyCard() {return 'source';}
}