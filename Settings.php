<?php
namespace Dfe\Stripe;
/** @method static Settings s() */
final class Settings extends \Df\StripeClone\Settings {
	/**
	 * 2016-03-08
	 * @override
	 * @see \Df\Payment\Settings::init()
	 * @used-by \Df\Payment\Method::action()
	 * @return void
	 */
	function init() {\Stripe\Stripe::setApiKey($this->privateKey());}

	/** @return bool */
	function isMerchantInUS() {return 'US' === $this->account()->{'country'};}

	/**
	 * 2016-03-14
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Metadata»
	 * @return string[]
	 */
	function metadata() {return $this->csv();}

	/**
	 * 2016-03-14
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Statement for Customer»
	 * @return string[]
	 */
	function statement() {return $this->v();}

	/**
	 * 2016-03-08
	 * https://stripe.com/docs/api/php#retrieve_account
	 * @return \Stripe\Account
	 */
	private function account() {return dfc($this, function() {
		$this->init();
		return \Stripe\Account::retrieve();
	});}
}


