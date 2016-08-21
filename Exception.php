<?php
namespace Dfe\Stripe;
use Stripe\Error\Base;
/**
 * 2016-08-19
 * @method Base prev()
 */
class Exception extends \Df\Payment\Exception {
	/**
	 * 2016-08-19
	 * @override
	 * @see \Df\Payment\Exception::__construct()
	 * @param $base $base
	 * @param array(string => mixed) $request [optional]
	 */
	public function __construct(Base $base, array $request = []) {
		$this->_request = $request;
		parent::__construct($base);
	}

	/**
	 * 2016-08-19
	 * @override
	 * @see \Df\Payment\Exception::message()
	 * @return string
	 */
	public function message() {return df_cc_n(
		'The Stripe request is failed.'
		,"Response:", df_json_encode_pretty($this->prev()->getJsonBody())
		,!$this->_request ? null : ['Request:', df_json_encode_pretty($this->_request)]
	);}

	/**
	 * 2016-07-17
	 * @override
	 * @see \Df\Payment\Exception::messageForCustomer()
	 * @return string
	 */
	public function messageForCustomer() {return df_payment_error_message($this->prev()->getMessage());}

	/**
	 * 2016-08-20
	 * @used-by \Dfe\Stripe\Exception::__construct()
	 * @used-by \Dfe\Stripe\Exception::message()
	 * @var array(string => mixed)
	 */
	private $_request;
}