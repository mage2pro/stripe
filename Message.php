<?php
namespace Dfe\Stripe;
use Magento\Sales\Model\Order\Payment\Transaction as T;
// 2016-08-20
class Message extends \Df\Core\A {
	/**
	 * 2016-08-20
	 * @return string
	 */
	public function card() {return $this->cardS($this->_card());}

	/**
	 * 2016-08-22
	 * @used-by \Dfe\Stripe\Message::card()
	 * @param array(string => mixed) $data
	 * @return string
	 */
	public static function cardS(array $data) {return
		sprintf('···· %s (%s)', dfa($data, 'last4'), dfa($data, 'brand'))
	;}

	/**
	 * 2016-08-20
	 * @return string
	 */
	public function country() {return df_country_ctn($this->_card('country'));}

	/**
	 * 2016-08-20
	 * @return string
	 */
	public function expires() {return implode(' / ', [
		$this->_card('exp_month'), $this->_card('exp_year')
	]);}

	/**
	 * 2016-08-20
	 * @return string
	 */
	public function id() {return $this['id'];}

	/**
	 * 2016-08-20
	 * @param T $t
	 * @return self
	 */
	public static function i(T $t) {
		/** @var string $class */
		$class = static::class;
		return new $class(df_json_decode(df_trans_raw_details($t, self::key())));
	}

	/**
	 * 2016-08-20
	 * @used-by \Dfe\Stripe\Message::i()
	 * @return string
	 */
	public static function key() {return df_class_last(static::class);}

	/**
	 * 2016-08-20
	 * @param string|null $key [optional]
	 * @return mixed|array(string => mixed)|null
	 */
	private function _card($key = null) {return $this->a(df_cc_path('source', $key));}
}


