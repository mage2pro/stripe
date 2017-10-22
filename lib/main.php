<?php
use Stripe\StripeObject as lO;
/**
 * 2017-10-22 Allowing $o to be an array makes my algorithms shorter.
 * @used-by \Dfe\Stripe\Facade\Card::__construct()
 * @used-by \Dfe\Stripe\Facade\O::toArray()
 * @param lO|array(string => mixed) $o
 * @return array(string => mixed)
 */
function dfe_stripe_a($o) {return is_array($o) ? $o : $o->__toArray(true);}