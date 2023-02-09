<?php
namespace Dfe\Stripe;
use Df\Payment\Settings\_3DS;
# 2016-03-08
/** @method static Settings s() */
final class Settings extends \Df\StripeClone\Settings {
	/**
	 * 2017-10-20
	 * @used-by \Dfe\Stripe\Init\Action::need3DS()
	 * @return _3DS
	 */
	function _3ds() {return dfc($this, function() {return new _3DS($this);});}

	/**
	 * 2016-03-08
	 * @override
	 * @see \Df\Payment\Settings::init()
	 * @used-by self::account()
	 * @used-by \Df\Payment\Method::action()
	 * @used-by \Dfe\Stripe\Block\Info::cardData()
	 * @used-by \Dfe\Stripe\Controller\CustomerReturn\Index::isSuccess()
	 * @used-by \Dfe\Stripe\Init\Action::preorder()
	 */
	function init():void {\Stripe\Stripe::setApiKey($this->privateKey());}

	/**
	 * 2016-03-08
	 * @used-by \Dfe\Stripe\ConfigProvider::config()
	 * @used-by \Dfe\Stripe\Method::minimumAmount()
	 * @return bool
	 */
	function isMerchantInUS() {return 'US' === $this->merchantCountry();}

	/**               
	 * 2017-10-15  
	 * @used-by self::isMerchantInUS()
	 * @used-by \Dfe\Stripe\FE\Currency::country()
	 * @return string
	 */
	function merchantCountry() {return $this->account()->{'country'};}

	/**
	 * 2016-03-08 https://stripe.com/docs/api/php#retrieve_account
	 * @used-by self::isMerchantInUS()
	 * @return \Stripe\Account
	 */
	private function account() {return dfc($this, function() {
		$this->init(); return \Stripe\Account::retrieve();
	});}
}