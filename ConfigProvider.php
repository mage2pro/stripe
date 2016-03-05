<?php
namespace Dfe\Stripe;
use Magento\Checkout\Model\ConfigProviderInterface;
class ConfigProvider implements ConfigProviderInterface {
	/**
	 * 2016-02-27
	 * @override
	 * @see \Magento\Checkout\Model\ConfigProviderInterface::getConfig()
	 * https://github.com/magento/magento2/blob/cf7df72/app/code/Magento/Checkout/Model/ConfigProviderInterface.php#L15-L20
	 * @return array(string => mixed)
	 */
	public function getConfig() {
		return ['payment' => [Method::CODE => [
			'isActive' => Settings::s()->enable()
			,'isTest' => Settings::s()->test()
			,'publishableKey' => Settings::s()->publishableKey()
		]]];
	}
}