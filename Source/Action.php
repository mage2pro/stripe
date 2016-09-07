<?php
namespace Dfe\Stripe\Source;
use Magento\Payment\Model\Method\AbstractMethod as M;
class Action extends \Df\Config\SourceT {
	/**
	 * 2016-03-07
	 * @override
	 * @see \Df\Config\Source::map()
	 * @used-by \Df\Config\Source::toOptionArray()
	 * @return array(string => string)
	 */
	protected function map() {return [
		M::ACTION_AUTHORIZE => 'Authorize'
		, M::ACTION_AUTHORIZE_CAPTURE => 'Capture'
		, self::REVIEW => 'Review'
	];}

	const REVIEW = 'review';
}