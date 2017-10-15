<?php
namespace Dfe\Stripe;
// 2016-03-08
/** @method static Settings s() */
final class Settings extends \Df\StripeClone\Settings {
	/**
	 * 2016-03-08
	 * @override
	 * @see \Df\Payment\Settings::init()
	 * @used-by account()
	 * @used-by \Df\Payment\Method::action()
	 */
	function init() {\Stripe\Stripe::setApiKey($this->privateKey());}

	/**
	 * 2016-03-08
	 * @used-by \Dfe\Stripe\ConfigProvider::config()
	 * @used-by \Dfe\Stripe\Method::minimumAmount()
	 * @return bool
	 */
	function isMerchantInUS() {return 'US' === $this->merchantCountry();}

	/**               
	 * 2017-10-15  
	 * @used-by isMerchantInUS()
	 * @used-by \Dfe\Stripe\FE\Currency::country()
	 * @return string
	 */
	function merchantCountry() {return $this->account()->{'country'};}

	/**
	 * 2016-03-08 https://stripe.com/docs/api/php#retrieve_account
	 * @used-by isMerchantInUS()
	 * @return \Stripe\Account
	 */
	private function account() {return dfc($this, function() {
		$this->init(); return \Stripe\Account::retrieve();
	});}
}