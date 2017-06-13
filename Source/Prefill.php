<?php
namespace Dfe\Stripe\Source;
// 2016-03-09
final class Prefill extends \Df\Config\Source {
	/**
	 * 2016-03-09
	 * https://stripe.com/docs/testing#cards
	 * https://mage2.pro/t/900
	 * @override
	 * @see \Df\Config\Source::map()
	 * @used-by \Df\Config\Source::toOptionArray()
	 * @return array(string => string)
	 */
	protected function map() {return [
		0 => 'No'
		,'4242424242424242' => 'Success (Visa)'
		,'5555555555554444' => 'Success (MasterCard)'
		,'4000000000000341' => 'Attempt to charge will fail'
		,'4000000000000002' => 'The charge will be declined'
		,'4100000000000019' => 'The charge will be fraudulent'
		,'4000000000000069' => 'The card will be treated as expired'
		,'4000000000000119' => 'A payment gateway processing error will be imitated'
		,'4000000000000127' => 'The charge will be declined because of an incorrect CVC'
	];}
}