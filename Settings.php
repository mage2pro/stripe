<?php
namespace Dfe\Stripe;
/** @method static Settings s() */
final class Settings extends \Df\Payment\Settings\StripeClone {
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
	public function init() {\Stripe\Stripe::setApiKey($this->testableP('secretKey'));}

	/** @return bool */
	public function isMerchantInUS() {return 'US' === $this->account()->{'country'};}

	/**
	 * 2016-03-14
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Metadata»
	 * @return string[]
	 */
	public function metadata() {return $this->csv();}

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
	private function account() {return dfc($this, function() {
		$this->init();
		return \Stripe\Account::retrieve();
	});}
}


