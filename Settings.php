<?php
namespace Dfe\Stripe;
use Magento\Framework\App\ScopeInterface as S;
/** @method static Settings s() */
class Settings extends \Df\Payment\Settings {
	/**
	 * 2016-03-15
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Payment Action for a New Customer»
	 * @param null|string|int|S $s [optional]
	 * @return string
	 */
	public function actionForNew($s = null) {return $this->v(__FUNCTION__, $s);}

	/**
	 * 2016-03-15
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Payment Action for a Returned Customer»
	 * @param null|string|int|S $s [optional]
	 * @return string
	 */
	public function actionForReturned($s = null) {return $this->v(__FUNCTION__, $s);}

	/**
	 * 2016-03-09
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Description»
	 * @param null|string|int|S $s [optional]
	 * @return string
	 */
	public function description($s = null) {return $this->v(__FUNCTION__, $s);}

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
	 * @param null|string|int|S $s [optional]
	 * @return string[]
	 */
	public function metadata($s = null) {return $this->csv(__FUNCTION__, $s);}

	/**
	 * 2016-03-09
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Prefill the Payment Form with Test Data?»
	 * @see \Dfe\Stripe\Source\Prefill::map()
	 * @param null|string|int|S $s [optional]
	 * @return string|false
	 */
	public function prefill($s = null) {return $this->bv(__FUNCTION__, $s);}

	/**
	 * 2016-03-02
	 * @param null|string|int|S $s [optional]
	 * @return string
	 */
	public function publishableKey($s = null) {
		return $this->test($s) ? $this->testPublishableKey($s) : $this->livePublishableKey($s);
	}

	/**
	 * 2016-03-14
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Statement for Customer»
	 * @param null|string|int|S $s [optional]
	 * @return string[]
	 */
	public function statement($s = null) {return $this->v(__FUNCTION__, $s);}

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
	 * @param null|string|int|S $s [optional]
	 * @return string
	 */
	private function livePublishableKey($s = null) {return $this->v(__FUNCTION__, $s);}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Live Secret Key»
	 * @param null|string|int|S $s [optional]
	 * @return string
	 */
	private function liveSecretKey($s = null) {return $this->p(__FUNCTION__, $s);}

	/**
	 * 2016-03-02
	 * @param null|string|int|S $s [optional]
	 * @return string
	 */
	private function secretKey($s = null) {
		return $this->test($s) ? $this->testSecretKey($s) : $this->liveSecretKey($s);
	}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Test Publishable Key»
	 * @param null|string|int|S $s [optional]
	 * @return string
	 */
	private function testPublishableKey($s = null) {return $this->v(__FUNCTION__, $s);}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Test Secret Key»
	 * @param null|string|int|S $s [optional]
	 * @return string
	 */
	private function testSecretKey($s = null) {return $this->p(__FUNCTION__, $s);}
}


