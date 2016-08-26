<?php
namespace Dfe\Stripe;
/** @method static Settings s() */
final class Settings extends \Df\Payment\Settings\BankCard {
	/**
	 * 2016-03-15
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Payment Action for a New Customer»
	 * @return string
	 */
	public function actionForNew() {return $this->v();}

	/**
	 * 2016-03-15
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Payment Action for a Returned Customer»
	 * @return string
	 */
	public function actionForReturned() {return $this->v();}

	/**
	 * 2016-03-09
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Description»
	 * @return string
	 */
	public function description() {return $this->v();}

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
	 * @return string[]
	 */
	public function metadata() {return $this->csv();}

	/**
	 * 2016-03-09
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Prefill the Payment Form with Test Data?»
	 * @see \Dfe\Stripe\Source\Prefill::map()
	 * @return string|false
	 */
	public function prefill() {return $this->bv();}

	/**
	 * 2016-03-02
	 * @return string
	 */
	public function publishableKey() {
		return $this->test() ? $this->testPublishableKey() : $this->livePublishableKey();
	}

	/**
	 * 2016-03-14
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Statement for Customer»
	 * @return string[]
	 */
	public function statement() {return $this->v();}

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
	 * @return string
	 */
	private function livePublishableKey() {return $this->v();}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Live Secret Key»
	 * @return string
	 */
	private function liveSecretKey() {return $this->p();}

	/**
	 * 2016-03-02
	 * @return string
	 */
	private function secretKey() {return $this->test() ? $this->testSecretKey() : $this->liveSecretKey();}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Test Publishable Key»
	 * @return string
	 */
	private function testPublishableKey() {return $this->v();}

	/**
	 * 2016-03-02
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Test Secret Key»
	 * @return string
	 */
	private function testSecretKey() {return $this->p();}
}


