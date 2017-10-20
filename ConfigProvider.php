<?php
namespace Dfe\Stripe;
// 2016-08-04
/** @method Settings s() */
final class ConfigProvider extends \Df\StripeClone\ConfigProvider {
	/**
	 * 2016-08-04
	 * @override
	 * @see \Df\StripeClone\ConfigProvider::config()
	 * @used-by \Df\Payment\ConfigProvider::getConfig()
	 * @return array(string => mixed)
	 */
	protected function config() {$s = $this->s(); $q = df_quote(); return [
		'3ds' => $s->_3ds()->enable_($q->getShippingAddress()->getCountryId(), $q->getCustomerId())
		,'isUS' => $s->isMerchantInUS()
		,'singleLineMode' => $s->b('singleLineMode')
	] + parent::config();}
}