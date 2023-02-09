<?php
namespace Dfe\Stripe\Facade;
use Stripe\StripeObject as _O;
# 2017-02-11
final class O extends \Df\StripeClone\Facade\O {
	/**
	 * 2017-02-11
	 * 2017-10-22 The previous implementation was: $o->getLastResponse()->json;
	 * @override
	 * @see \Df\StripeClone\Facade\O::toArray()
	 * @used-by \Df\StripeClone\Method::transInfo()
	 * @param _O $o
	 * @return array(string => mixed)
	 */
	function toArray($o):array {return dfe_stripe_a($o);}
}