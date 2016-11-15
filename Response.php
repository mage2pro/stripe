<?php
namespace Dfe\Stripe;
// 2016-08-20
class Response extends Message {
	/**
	 * 2016-08-20
	 * @return string
	 */
	public function card() {return $this->cardS($this->_card());}

	/**
	 * 2016-08-22
	 * @used-by \Dfe\Stripe\Response::card()
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
	 * @param string|null $key [optional]
	 * @return string
	 */
	private function _card($key = null) {return $this->a(df_cc_path('source', $key));}
}