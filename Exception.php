<?php
namespace Dfe\Stripe;
/**
 * 2020-01-22
 * «Class Stripe\Error\Base does not exist»: https://github.com/mage2pro/stripe/issues/86
 * https://github.com/stripe/stripe-php/blob/v5.3.0/lib/Error/Base.php
 * The \Stripe\Error\Base class has been deleted from `stripe/stripe-php` since 7.0.0.
 */
use Stripe\Exception\ApiErrorException as Base;
/**
 * 2016-08-19
 * @method Base prev()
 */
final class Exception extends \Df\Payment\Exception {
	/**
	 * 2016-08-19
	 * @override
	 * @see \Df\Core\Exception::__construct()
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
	 * @used-by df_xts()
	 */
	function message():string {return df_api_rr_failed($this, $this->prev()->getJsonBody(), $this->_request);}

	/**
	 * 2016-07-17
	 * @override
	 * @see \Df\Core\Exception::messageC()
	 * @used-by \Df\Payment\PlaceOrderInternal::message()
	 */
	function messageC():string {return dfp_error_message(df_xts($this->prev()));}

	/**
	 * 2016-08-20
	 * @used-by \Dfe\Stripe\Exception::__construct()
	 * @used-by \Dfe\Stripe\Exception::message()
	 * @var array(string => mixed)
	 */
	private $_request;
}