<?php
namespace Dfe\Stripe;
use Stripe\Error\Base;
/**
 * 2016-08-19
 * @method Base prev()
 */
final class Exception extends \Df\Payment\Exception {
	/**
	 * 2016-08-19
	 * @override
	 * @see \Df\Core\Exception::__construct()
	 * @param $base $base
	 * @param array(string => mixed) $request [optional]
	 */
	function __construct(Base $base, array $request = []) {
		$this->_request = $request;
		parent::__construct($base);
	}

	/**
	 * 2016-08-19
	 * @override
	 * @see \Df\Core\Exception::message()
	 * @return string
	 */
	function message() {return df_api_rr_failed($this, $this->prev()->getJsonBody(), $this->_request);}

	/**
	 * 2016-07-17
	 * @override
	 * @see \Df\Core\Exception::messageC()
	 * @return string
	 */
	function messageC() {return dfp_error_message($this->prev()->getMessage());}

	/**
	 * 2016-08-20
	 * @used-by \Dfe\Stripe\Exception::__construct()
	 * @used-by \Dfe\Stripe\Exception::message()
	 * @var array(string => mixed)
	 */
	private $_request;
}