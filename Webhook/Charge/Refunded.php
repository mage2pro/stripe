<?php
// 2017-01-04
namespace Dfe\Stripe\Webhook\Charge;
use Dfe\Stripe\Method as M;
final class Refunded extends \Dfe\Stripe\Webhook implements \Df\StripeClone\Webhook\IRefund {
	/**
	 * 2017-01-17
	 * В валюте заказа (платежа), в формате платёжной системы (копейках).
	 * @override
	 * @see \Df\StripeClone\Webhook\IRefund::amount()
	 * @used-by \Df\StripeClone\WebhookStrategy\Charge\Refunded::handle()
	 * @return int
	 */
	function amount() {return df_last($this->ro('refunds/data'))['amount'];}

	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\Webhook::currentTransactionType()
	 * @used-by \Df\StripeClone\Webhook::id()
	 * @used-by \Df\StripeClone\WebhookStrategy::currentTransactionType()
	 * @return string
	 */
	function currentTransactionType() {return M::T_REFUND;}

	/**
	 * 2017-01-19
	 * 2017-02-14
	 * Метод должен вернуть идентификатор операции (не платежа!) в платёжной системе.
	 * Он нужен нам для избежания обработки оповещений о возвратах, инициированных нами же
	 * из административной части Magento: @see \Df\StripeClone\Method::_refund()
	 * Это должен быть тот же самый идентификатор,
	 * который возвращает @see \Dfe\Stripe\Facade\Refund::transId()
	 * @override
	 * @see \Df\StripeClone\Webhook\IRefund::eTransId()
	 * @used-by \Df\StripeClone\WebhookStrategy\Charge\Refunded::handle()
	 * @return string
	 */
	function eTransId() {return df_last($this->ro('refunds/data'))['balance_transaction'];}

	/**
	 * 2016-12-16
	 * @override
	 * @see \Dfe\Stripe\Webhook::parentTransactionType()
	 * @used-by \Dfe\Stripe\Webhook::adaptParentId()
	 * @return string
	 */
	protected function parentTransactionType() {return M::T_CAPTURE;}
}