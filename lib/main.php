<?php
use Dfe\Stripe\Method as M;
use Stripe\Source as lSource;
use Stripe\StripeObject as lO;
/**
 * 2017-10-22 Allowing $o to be an array makes my algorithms shorter.
 * 2020-07-08
 * 1) «Call to undefined method Stripe\Source::__toArray() in vendor/mage2pro/stripe/lib/main.php:14»:
 * https://github.com/mage2pro/stripe/issues/87
 * 2) The @see \Stripe\StripeObject::__toArray() method was removed from Stripe PHP SDK ≥ 7.
 * https://github.com/stripe/stripe-php/blob/v6.0.0/lib/StripeObject.php#L351-L358
 * @used-by \Dfe\Stripe\Facade\Card::__construct()
 * @used-by \Dfe\Stripe\Facade\Card::__construct()
 * @used-by \Dfe\Stripe\Facade\O::toArray()
 * @used-by \Dfe\Stripe\Init\Action::redirectUrl()
 * @param lO|array(string => mixed) $o
 * @return array(string => mixed)
 */
function dfe_stripe_a($o):array {return is_array($o) ? $o : $o->toArray();}

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
function dfe_stripe_source(string $id) {return dfcf(function($id) {
	dfps(M::class)->init(); return lSource::retrieve($id);
}, [$id]);}