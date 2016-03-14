<?php
namespace Dfe\Stripe;
use Magento\Framework\App\ScopeInterface;
class Settings extends \Df\Core\Settings {
	/**
	 * 2016-03-09
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Payment Description Template»
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return string
	 */
	public function description($s = null) {return $this->v(__FUNCTION__, $s);}

	/**
	 * 2016-02-27
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Enable?»
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return bool
	 */
	public function enable($s = null) {return $this->b(__FUNCTION__, $s);}

	/**
	 * 2016-03-08
	 * @return void
	 */
	public function init() {\Stripe\Stripe::setApiKey($this->secretKey());}

	/** @return bool */
	public function isMerchantInUS() {return 'US' === $this->account()->{'country'};}

	/**
	 * 2016-03-14
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Metadata»
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return string[]
	 */
	public function metadata($s = null) {return $this->csv(__FUNCTION__, $s);}

	/**
	 * 2016-03-02
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return string
	 */
	public function publishableKey($s = null) {
		return $this->test($s) ? $this->testPublishableKey($s) : $this->livePublishableKey($s);
	}

	/**
	 * 2016-03-09
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Prefill the Payment Form with Test Data?»
	 * @see \Dfe\Stripe\Source\Prefill::map()
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return string|false
	 */
	public function prefill($s = null) {return $this->bv(__FUNCTION__, $s);}

	/**
	 * @override
	 * @used-by \Df\Core\Settings::v()
	 * @return string
	 */
	protected function prefix() {return 'df_payment/stripe/';}

	/**
	 * 2016-03-08
	 * https://stripe.com/docs/api/php#retrieve_account
	 * @return \Stripe\Account
	 */
	private function account() {
		if (!isset($this->{__METHOD__})) {
			$this->init();
			$this->{__METHOD__} = \Stripe\Account::retrieve();
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Live Publishable Key»
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return string
	 */
	private function livePublishableKey($s = null) {return $this->v(__FUNCTION__, $s);}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Live Secret Key»
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return string
	 */
	private function liveSecretKey($s = null) {return $this->p(__FUNCTION__, $s);}

	/**
	 * 2016-03-02
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return string
	 */
	private function secretKey($s = null) {
		return $this->test($s) ? $this->testSecretKey($s) : $this->liveSecretKey($s);
	}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Test Mode?»
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return bool
	 */
	public function test($s = null) {return $this->b(__FUNCTION__, $s);}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Test Publishable Key»
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return string
	 */
	private function testPublishableKey($s = null) {return $this->v(__FUNCTION__, $s);}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Test Secret Key»
	 * @param null|string|int|ScopeInterface $s [optional]
	 * @return string
	 */
	private function testSecretKey($s = null) {return $this->p(__FUNCTION__, $s);}

	/** @return $this */
	public static function s() {static $r; return $r ? $r : $r = df_o(__CLASS__);}
}


