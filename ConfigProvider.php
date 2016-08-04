<?php
namespace Dfe\Stripe;
/** @method Settings s() */
class ConfigProvider extends \Df\Payment\ConfigProvider {
	/**
	 * 2016-08-04
	 * @override
	 * @see \Df\Payment\ConfigProvider::custom()
	 * @used-by \Df\Payment\ConfigProvider::getConfig()
	 * @return array(string => mixed)
	 */
	protected function custom() {return [
		'isUS' => $this->s()->isMerchantInUS()
		,'prefill' => $this->s()->prefill()
		,'publishableKey' => $this->s()->publishableKey()
	];}
}