<?php
namespace Dfe\Stripe;
use Magento\Framework\App\ScopeInterface as IScope;
use Magento\Store\Model\Store;
/**
 * 2017-10-12
 * «JCB, Discover, and Diners Club cards can only be charged in USD»:
 * https://github.com/mage2pro/stripe/issues/28
 * @method Method m()
 */
final class Currency extends \Df\Payment\Currency {
	/**
	 * 2017-10-12
	 * Note 1. «JCB, Discover, and Diners Club cards can only be charged in USD»:
	 * https://github.com/mage2pro/stripe/issues/28
	 * Note 2. 
	 * `brand`: «Card brand.
	 * Can be `Visa`, `American Express`, `MasterCard`, `Discover`, `JCB`, `Diners Club`, or `Unknown`.»
	 * https://stripe.com/docs/api#card_object-brand
	 * @override
	 * @see \Df\Payment\Currency::_iso3()
	 * @used-by \Df\Payment\Currency::iso3()
	 * @param null|string|int|IScope|Store $s [optional]
	 * @return string
	 */
	protected function _iso3($s = null) {return
		in_array($this->m()->cardType(), ['Discover', 'JCB', 'Diners Club']) ? 'USD' : parent::_iso3($s)
	;}
}