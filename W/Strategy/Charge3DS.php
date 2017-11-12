<?php
namespace Dfe\Stripe\W\Strategy;
use Df\Payment\Token;
use Df\Payment\W\Strategy\ConfirmPending;
use Df\StripeClone\W\Event as Ev;
use Dfe\Stripe\Facade\Token as fToken;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Payment as OP;
/**
 * 2017-11-10
 * @used-by \Dfe\Stripe\W\Handler\Source
 * @method Ev e()
 */
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
		 * 2017-11-12
		 * @used-by \Df\StripeClone\Payer::token()
		 * https://github.com/mage2pro/core/blob/3.3.4/StripeClone/Payer.php#L145-L155
		 */
		dfp_add_info($op, [
			/**
			 * 2017-11-12
			 * Note 1.
			 * A `source.chargeable` event for a derived single-use 3D Secure source:
			 * https://mage2.pro/t/4895
			 * Note 2.
			 * It looks like we should use a derived single-use 3D Secure source here,
			 * not an initial reusable source:
			 * *) "A charge for the test card with required 3D Secure verification (4000000000003063) fails:
			 * «Your card was declined»": https://github.com/mage2pro/stripe/issues/46
			 * *) "A derived single-use 3D Secure source": https://mage2.pro/t/4894
			 * *) "An initial reusable source for a card which requires a 3D Secure verification":
			 * https://mage2.pro/t/4893
			 * Note 3.
			 * I intentionally do not add the «new_» prefix here,
			 * because this source is single-use, and I do not plan to attach it to the customer anyway.
			 */
			Token::KEY => $this->e()->pid()
			/**
			 * 2017-11-12
			 * We do not need to set the bank card type: @see \Dfe\Stripe\Method::$II_CARD_TYPE
			 * https://github.com/mage2pro/stripe/blob/2.4.0/Method.php#L170-L175
			 * because it was already saved in the payment before the 3D Secure verification,
			 * and @see \Dfe\Stripe\Method::cardType() will retrieve it with the code:
			 * 		$this->iia(self::$II_CARD_TYPE)
			 * https://github.com/mage2pro/stripe/blob/765e3bb6/Method.php#L30
			 * The bank card type is used only by @see \Dfe\Stripe\Currency::_iso3():
			 *		protected function _iso3($s = null) {return
			 *			in_array($this->m()->cardType(), ['Discover', 'JCB', 'Diners Club'])
			 * 				? 'USD' : parent::_iso3($s)
			 *		;}
			 * https://github.com/mage2pro/stripe/blob/2.4.0/Currency.php#L12-L25
			 * It will return the same currency
			 * which we have already passed to Stripe before the 3D Secure verification:
			 * @see \Dfe\Stripe\P\_3DS::p():
			 * 		'currency' => $i->currencyC()
			 * https://github.com/mage2pro/stripe/blob/d66c3153/P/_3DS.php#L7-L18
			 */
		]);
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