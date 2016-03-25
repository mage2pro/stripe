<?php
namespace Dfe\Stripe\Handler\Charge;
use Dfe\Stripe\Handler\Charge;
use Magento\Sales\Model\Order\Payment;
// 2016-03-25
// https://stripe.com/docs/api#event_types-charge.captured
// Occurs whenever a previously uncaptured charge is captured.
class Captured extends Charge {
	/**
	 * 2016-03-25
	 * @override
	 * @see \Dfe\Stripe\Handler::_process()
	 * @used-by \Dfe\Stripe\Handler::process()
	 * @return mixed
	 */
	protected function process() {
		/** @var int $paymentId */
		$paymentId = df_fetch_one('sales_payment_transaction', 'payment_id', [
			'txn_id' => $this->o('id')
		]);
		/** @var Payment $payment */
		$payment = df_om()->create(Payment::class);
		$payment->load($paymentId);
		return $paymentId;
	}
}