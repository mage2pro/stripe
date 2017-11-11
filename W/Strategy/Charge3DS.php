<?php
namespace Dfe\Stripe\W\Strategy;
use Df\Payment\W\Strategy\ConfirmPending;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Payment as OP;
// 2017-11-10
/** @used-by \Dfe\Stripe\W\Handler\Source */
final class Charge3DS extends \Df\Payment\W\Strategy {
	/**
	 * 2017-11-10
	 * @override
	 * @see \Df\Payment\W\Strategy::_handle()
	 * @used-by \Df\Payment\W\Strategy::::handle()
	 */
	protected function _handle() {
		dfp_webhook_case($op = $this->op(), false); /** @var OP $op */
		/**
		 * 2017-11-11
		 * It is important that @see \Df\StripeClone\Method::isGateway() returns `true`.
		 * Otherwise, such implementation will not work because of the following code:
		 * @see \Magento\Sales\Model\Order\Invoice::register():
		 *   $captureCase = $this->getRequestedCaptureCase();
		 *		if ($this->canCapture()) {
		 *			if ($captureCase) {
		 *				if ($captureCase == self::CAPTURE_ONLINE) {
		 *					$this->capture();
		 *				}
		 *				elseif ($captureCase == self::CAPTURE_OFFLINE) {
		 *					$this->setCanVoidFlag(false);
		 *					$this->pay();
		 *				}
		 *			}
		 *		}
		 *		elseif (
		 *			!$order->getPayment()->getMethodInstance()->isGateway()
		 *			|| $captureCase == self::CAPTURE_OFFLINE
		 *		) {
		 *			if (!$order->getPayment()->getIsTransactionPending()) {
		 *				$this->setCanVoidFlag(false);
		 *				$this->pay();
		 *			}
		 *		}
		 * The code is the same in Magento 2.0.0 - 2.2.1:
		 * https://github.com/magento/magento2/blob/2.0.0/app/code/Magento/Sales/Model/Order/Invoice.php#L599-L614
		 * https://github.com/magento/magento2/blob/2.2.1/app/code/Magento/Sales/Model/Order/Invoice.php#L611-L626
		 * In this scenario isGateway() is important
		 * to avoid the @see \Magento\Sales\Model\Order\Invoice::pay() call
		 * (which marks order as paid without any actual PSP API calls).
		 * "dfp_due() should support the Stripe's 3D Secure verification scenario":
		 * https://github.com/mage2pro/core/issues/46
		 */
		$this->delegate(ConfirmPending::class);
	}
}