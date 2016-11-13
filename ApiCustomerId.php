<?php
namespace Dfe\Stripe;
use Magento\Customer\Model\Customer as C;
// 2016-08-24
class ApiCustomerId {
	/**
	 * 2016-08-24
	 * @param C|null $c [optional]
	 * @return string|null
	 */
	public static function get($c = null) {return df_customer_info_get($c, self::$KEY);}

	/**
	 * 2016-08-24
	 * Если $id равно null, то ключ удалится: @see dfo()
	 * @param string|null $id
	 */
	public static function save($id) {df_customer_info_save(self::$KEY, $id);}

	/**
	 * 2016-08-24
	 * @var string
	 */
	private static $KEY = 'stripe';
}