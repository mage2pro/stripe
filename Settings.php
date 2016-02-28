<?php
namespace Dfe\Stripe;
use Magento\Framework\App\ScopeInterface;
class Settings extends \Df\Core\Settings {
	/**
	 * 2016-02-27
	 * «Mage2.PRO» → «Payment» → «Stripe» → «Enable?»
	 * @param null|string|int|ScopeInterface $scope [optional]
	 * @return bool
	 */
	public function enable($scope = null) {return $this->b(__FUNCTION__, $scope);}

	/**
	 * @override
	 * @used-by \Df\Core\Settings::v()
	 * @return string
	 */
	protected function prefix() {return 'df_payment/stripe/';}

	/** @return $this */
	public static function s() {static $r; return $r ? $r : $r = df_o(__CLASS__);}
}


