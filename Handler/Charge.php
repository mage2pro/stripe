<?php
namespace Dfe\Stripe\Handler;
use Df\Payment\Transaction;
use Df\Sales\Model\Order as DfOrder;
use Df\Sales\Model\Order\Payment as DfPayment;
use Dfe\Stripe\Handler;
use Magento\Framework\Exception\LocalizedException as LE;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
abstract class Charge extends Handler {
	/**
	 * 2016-03-28
	 * @used-by \Dfe\Stripe\Handler::p()
	 * @override
	 * @see \Dfe\Stripe\Handler::eligible()
	 * @return bool
	 */
	protected function eligible() {return !!$this->payment();}

	/**
	 * 2016-03-26
	 * @return Order|DfOrder
	 * @throws LE
	 */
	protected function order() {return $this->transaction()->order();}

	/**
	 * 2016-03-26
	 * Ситуация, когда платёж не найден, является нормальной,
	 * потому что к одной учётной записи Stripe может быть привязано несколько магазинов,
	 * и Stripe будет оповещать сразу все магазины о событиях одного из них.
	 * Магазину надо уметь различать свои события и чужие,
	 * и мы делаем это именно по идентификатору транзакции.
	 * @return Payment|DfPayment|null
	 */
	protected function payment() {return $this->transaction()->payment();}

	/**
	 * 2016-03-27
	 * https://stripe.com/docs/api#charge_object-id
	 * @return string
	 */
	protected function id() {return $this->o('id');}

	/**
	 * 2016-05-05
	 * @return Transaction
	 */
	private function transaction() {return dfc($this, function() {
		/** @var Transaction $result */
		$result = Transaction::sp($this->id());
		!$result->payment() ?: dfp_webhook_case($result->payment());
		return $result;
	});}
}