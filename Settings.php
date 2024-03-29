<?php
namespace Dfe\Stripe;
use Df\Payment\Settings\_3DS;
use Stripe\Account;
# 2016-03-08
/** @method static Settings s() */
final class Settings extends \Df\StripeClone\Settings {
	/**
	 * 2017-10-20
	 * @used-by \Dfe\Stripe\Init\Action::need3DS()
	 */
	function _3ds():_3DS {return dfc($this, function() {return new _3DS($this);});}

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
	 */
	function isMerchantInUS():bool {return 'US' === $this->merchantCountry();}

	/**               
	 * 2017-10-15  
	 * @used-by self::isMerchantInUS()
	 * @used-by \Dfe\Stripe\FE\Currency::country()
	 */
	function merchantCountry():string {return $this->account()->{'country'};}

	/**
	 * 2016-03-08 https://stripe.com/docs/api/php#retrieve_account
	 * @used-by self::isMerchantInUS()
	 */
	private function account():Account {return dfc($this, function() {$this->init(); return Account::retrieve();});}
}