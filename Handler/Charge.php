<?php
namespace Dfe\Stripe\Handler;
use Df\Sales\Model\Order as DfOrder;
use Df\Sales\Model\Order\Payment as DfPayment;
use Dfe\Stripe\Handler;
use Dfe\Stripe\Method;
use Magento\Framework\Exception\LocalizedException as LE;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Api\Data\OrderInterface;
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
	protected function order() {
		if (!isset($this->{__METHOD__})) {
			/** @var Order $result */
			$result = $this->payment()->getOrder();
			if (!$result->getId()) {
				throw new LE(__('The order no longer exists.'));
			}
			/**
			 * 2016-03-26
			 * Очень важно! Иначе order создать свой экземпляр payment:
			 * @used-by \Magento\Sales\Model\Order::getPayment()
			 */
			$result[OrderInterface::PAYMENT] = $this->payment();
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-03-26
	 * Ситуация, когда платёж не найден, является нормальной,
	 * потому что к одной учётной записи Stripe может быть привязано несколько магазинов,
	 * и Stripe будет оповещать сразу все магазины о событиях одного из них.
	 * Магазину надо уметь различать свои события и чужие,
	 * и мы делаем это именно по идентификатору транзакции.
	 * @return Payment|DfPayment|null
	 */
	protected function payment() {
		if (!isset($this->{__METHOD__})) {
			/** @var int|null $paymentId */
			$paymentId = df_fetch_one('sales_payment_transaction', 'payment_id', [
				'txn_id' => $this->id()
			]);
			/** @var Payment|null $result */
			if (!$paymentId) {
				$result = null;
			}
			else {
				$result = df_load(Payment::class, $paymentId);
				$result->setData(Method::ALREADY_DONE, true);
			}
			$this->{__METHOD__} = df_n_set($result);
		}
		return df_n_get($this->{__METHOD__});
	}

	/**
	 * 2016-03-27
	 * https://stripe.com/docs/api#charge_object-id
	 * @return string
	 */
	protected function id() {return $this->o('id');}
}