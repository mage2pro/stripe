<?php
namespace Dfe\Stripe;
// 2016-08-20
class Response extends Message {
	/**
	 * 2016-08-20
	 * @return string
	 */
	public function card() {
		return sprintf('···· %s (%s)', $this->source('last4'), $this->source('brand'));
	}

	/**
	 * 2016-08-20
	 * @return string
	 */
	public function country() {return df_country_ctn($this->source('country'));}

	/**
	 * 2016-08-20
	 * @return string
	 */
	public function expires() {
		return implode(' / ', [$this->source('exp_month'), $this->source('exp_year')]);
	}

	/**
	 * 2016-08-20
	 * @return string
	 */
	public function id() {return $this['id'];}

	/**
	 * 2016-08-20
	 * @param string $key
	 * @return string
	 */
	private function source($key) {return $this->a('source/' . $key);}
}


