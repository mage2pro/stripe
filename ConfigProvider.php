<?php
namespace Dfe\Stripe;
use Magento\Customer\Model\Customer;
/** @method Settings s() */
class ConfigProvider extends \Df\Payment\ConfigProvider\BankCard {
	/**
	 * 2016-08-04
	 * @override
	 * @see \Df\Payment\ConfigProvider::config()
	 * @used-by \Df\Payment\ConfigProvider::getConfig()
	 * @return array(string => mixed)
	 */
	protected function config() {return [
		'isUS' => $this->s()->isMerchantInUS()
		,'prefill' => $this->s()->prefill()
		,'publishableKey' => $this->s()->publishableKey()
	] + parent::config();}

	/**
	 * 2016-08-22
	 * @override
	 * @see \Df\Payment\ConfigProvider\BankCard::savedCards()
	 * @used-by \Df\Payment\ConfigProvider\BankCard::config()
	 * @return array(string => string)
	 */
	protected function savedCards() {
		/** @var array(mixed => mixed) $result */
		$result = [];
		/** @var string|null $stripeId */
		$stripeId = df_customer_info_get(null, Charge::CUSTOMER_INFO_KEY);
		if ($stripeId) {
			$this->s()->init();
			/** @var \Stripe\Customer $c */
			$c = \Stripe\Customer::retrieve($stripeId);
			foreach ($c->sources->{'data'} as $card) {
				/** @var \Stripe\Card $card */
				$result[]= ['id' => $card->id, 'label' => Response::cardS($card->__toArray())];
			}
		}
		return $result;
	}
}