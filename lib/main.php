<?php
use Dfe\Stripe\Method as M;
use Stripe\Source as lSource;
use Stripe\StripeObject as lO;
/**
 * 2017-10-22 Allowing $o to be an array makes my algorithms shorter.
 * @used-by \Dfe\Stripe\Facade\Card::__construct()
 * @used-by \Dfe\Stripe\Facade\Card::__construct()
 * @used-by \Dfe\Stripe\Facade\O::toArray()
 * @used-by \Dfe\Stripe\Init\Action::redirectUrl()
 * @param lO|array(string => mixed) $o
 * @return array(string => mixed)
 */
function dfe_stripe_a($o) {return is_array($o) ? $o : $o->__toArray(true);}

/**
 * 2017-11-12
 * @used-by \Dfe\Stripe\Block\Info::cardData()
 * @used-by \Dfe\Stripe\Controller\CustomerReturn\Index::isSuccess()
 * @used-by \Dfe\Stripe\Facade\Card::__construct()
 * @used-by \Dfe\Stripe\Init\Action::sourceInitial()
 * @used-by \Dfe\Stripe\Payer::tokenIsSingleUse()
 * @param string $id
 * @return lSource
 */
function dfe_stripe_source($id) {return dfcf(function($id) {
	dfps(M::class)->init(); return lSource::retrieve($id);
}, [$id]);}