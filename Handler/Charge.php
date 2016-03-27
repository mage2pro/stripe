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
	 * @return Payment|DfPayment
	 */
	protected function payment() {
		if (!isset($this->{__METHOD__})) {
			/** @var int $paymentId */
			$paymentId = df_fetch_one('sales_payment_transaction', 'payment_id', [
				'txn_id' => $this->id()
			]);
			df_assert($paymentId, "Transaction not found: {$this->id()}.");
			$this->{__METHOD__} = df_load(Payment::class, $paymentId);
			$this->{__METHOD__}->setData(Method::ALREADY_DONE, true);
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-03-27
	 * https://stripe.com/docs/api#charge_object-id
	 * @return string
	 */
	protected function id() {return $this->o('id');}
}