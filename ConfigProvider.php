<?php
namespace Dfe\Stripe;
/** @method Settings s() */
final class ConfigProvider extends \Df\StripeClone\ConfigProvider {
	/**
	 * 2016-08-04
	 * @override
	 * @see \Df\Payment\ConfigProvider::config()
	 * @used-by \Df\Payment\ConfigProvider::getConfig()
	 * @return array(string => mixed)
	 */
	protected function config() {return [
		'isUS' => $this->s()->isMerchantInUS()
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
		/** @var string|null $apiId */
		$apiId = ApiCustomerId::get();
		if ($apiId) {
			$this->s()->init();
			/** @var ApiCustomer $c */
			$c = ApiCustomer::retrieve($apiId);
			if ($c->isDeleted()) {
				ApiCustomerId::save(null);
			}
			else {
				$result = $c->cards();
			}
		}
		return $result;
	}
}