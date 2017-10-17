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
	protected function config() {$s = $this->s(); return [
		'isUS' => $s->isMerchantInUS(), 'singleLineMode' => $s->b('singleLineMode')
	] + parent::config();}
}