<?php
// 2017-01-04
namespace Dfe\Stripe\W\Handler\Charge;
use Df\StripeClone\W\Strategy\Charge\Captured as Strategy;
use Dfe\Stripe\Method as M;
final class Captured extends \Dfe\Stripe\W\Handler {
	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\W\Handler::currentTransactionType()
	 * @used-by \Df\StripeClone\W\Handler::id()
	 * @used-by \Df\StripeClone\W\Strategy::currentTransactionType()
	 * @return string
	 */
	function currentTransactionType() {return M::T_CAPTURE;}

	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\W\Handler::parentTransactionType()
	 * @used-by \Df\StripeClone\W\Handler::adaptParentId()
	 * @return string
	 */
	protected function parentTransactionType() {return M::T_AUTHORIZE;}
	
	/**
	 * 2017-03-13
	 * @override
	 * @see \Df\StripeClone\W\Handler::strategyC()
	 * @used-by \Df\StripeClone\W\Handler::_handle()
	 * @return string
	 */
	protected function strategyC() {return Strategy::class;}	
}