<?php
// 2017-01-04
namespace Dfe\Stripe\W\Handler\Charge;
use Df\Payment\W\Strategy\Refund as Strategy;
final class Refunded extends \Df\StripeClone\W\Handler implements \Df\Payment\W\IRefund {
	/**
	 * 2017-01-17
	 * В валюте заказа (платежа), в формате платёжной системы (копейках).
	 * @override
	 * @see \Df\Payment\W\IRefund::amount()
	 * @used-by \Df\Payment\W\Strategy\Refund::_handle()
	 * @return int
	 */
	function amount() {return df_last($this->e()->ro('refunds/data'))['amount'];}

	/**
	 * 2017-01-19
	 * 2017-02-14
	 * Метод должен вернуть идентификатор операции (не платежа!) в платёжной системе.
	 * Он нужен нам для избежания обработки оповещений о возвратах, инициированных нами же
	 * из административной части Magento: @see \Df\StripeClone\Method::_refund()
	 * Это должен быть тот же самый идентификатор,
	 * который возвращает @see \Dfe\Stripe\Facade\Refund::transId()
	 * @override
	 * @see \Df\Payment\W\IRefund::eTransId()
	 * @used-by \Df\Payment\W\Strategy\Refund::_handle()
	 * @return string
	 */
	function eTransId() {return df_last($this->e()->ro('refunds/data'))['balance_transaction'];}

	/**
	 * 2017-03-13
	 * @override
	 * @see \Df\Payment\W\Handler::strategyC()
	 * @used-by \Df\Payment\W\Handler::handle()
	 * @return string
	 */
	protected function strategyC() {return Strategy::class;}
}