<?php
namespace Dfe\Stripe;
use Df\Core\Exception as DFE;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Creditmemo as CM;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment as OP;
use Magento\Sales\Model\Order\Payment\Transaction as T;
use Stripe\Error\Base as lException;
/** @method Settings s() */
final class Method extends \Df\StripeClone\Method {
	/**
	 * 2016-03-08
	 * @override
	 * @see \Df\Payment\Method::canCapturePartial()
	 * @return bool
	 */
	function canCapturePartial() {return true;}

	/**
	 * 2016-11-13
	 * https://stripe.com/docs/api/php#create_charge-amount
	 * https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
	 * @override
	 * @see \Df\Payment\Method::amountFactorTable()
	 * @used-by \Df\Payment\Method::amountFactor()
	 * @return int
	 */
	protected function amountFactorTable() {return [
		1 => 'BIF,CLP,DJF,GNF,JPY,KMF,KRW,MGA,PYG,RWF,VND,VUV,XAF,XOF,XPF'
	];}

	/**
	 * 2017-02-08
	 * @override
	 * The result should be in the basic monetary unit (like dollars), not in fractions (like cents).
	 *
	 * [Stripe] What are the minimum and maximum amount limitations on a single payment?
	 * https://mage2.pro/t/2689
	 *
	 * 1) «What is the minimum amount I can charge with Stripe?»
	 * https://support.stripe.com/questions/what-is-the-minimum-amount-i-can-charge-with-stripe
	 *
	 * 2) «What is the maximum amount I can charge with Stripe?»
	 * https://support.stripe.com/questions/what-is-the-maximum-amount-i-can-charge-with-stripe
	 * «Regardless of the currency, there is a technical limitation of 8 digits
	 * (e.g. $999,999.99, or ¥99,999,999), though we don’t impose any other limitations atop that.»
	 * @see \Df\Payment\Method::amountLimits()
	 * @used-by \Df\Payment\Method::isAvailable()
	 * @return \Closure
	 */
	protected function amountLimits() {return function($c) {return [
		$this->minimumAmount($c), $this->amountParse(99999999)
	];};}

	/**
	 * 2016-12-28
	 * @override
	 * @see \Df\Payment\Method::convertException()
	 * @used-by \Df\Payment\Method::action()
	 * @param \Exception|lException $e
	 * @return \Exception
	 */
	protected function convertException(\Exception $e) {return
		$e instanceof lException ? new Exception($e) : $e
	;}

	/**
	 * 2016-12-26
	 * Хотя Stripe использует для страниц транзакций адреса вида
	 * https://dashboard.stripe.com/test/payments/<id>
	 * адрес без части «test» также успешно работает (даже в тестовом режиме).
	 * Использую именно такие адреса, потому что я не знаю,
	 * какова часть вместо «test» в промышленном режиме.
	 * @override
	 * @see \Df\StripeClone\Method::transUrlBase()
	 * @used-by \Df\StripeClone\Method::transUrl()
	 * @param T $t
	 * @return string
	 */
	protected function transUrlBase(T $t) {return 'https://dashboard.stripe.com/payments';}

	/**
	 * 2017-02-08
	 * The result should be in the basic monetary unit (like dollars), not in fractions (like cents).
	 * «If your business is based in the United States and only processes payments in US dollars,
	 * then it’s pretty straightforward.
	 * In order to make sure each charge covers the necessary fees
	 * and you don’t end up losing money on the transaction,
	 * we require a minimum of $0.50 for each charge.
	 *
	 * If your business is based in the United States and you process payments in other currencies,
	 * then the minimum will be the equivalent of $0.50 after the charge is converted to US dollars.»
	 * https://support.stripe.com/questions/what-is-the-minimum-amount-i-can-charge-with-stripe#businesses-in-the-united-states
	 *
	 * «For businesses that are not based in the United States,
	 * the minimum charge depends on the currency for your bank account.
	 *
	 * Charges that are made in the same currency as your bank account
	 * will have the minimum shown in the list below.
	 * Charges that are made in other currencies will have the equivalent minimum
	 * after the charge is converted to your bank account’s currency.»
	 *
	 * https://support.stripe.com/questions/what-is-the-minimum-amount-i-can-charge-with-stripe#but-what-if-my-business-isnt-based-in-the-united-states
	 *
	 * @used-by amountLimits()
	 * @param string $c
	 * @return float
	 */
	private function minimumAmount($c) {return
		$this->s()->isMerchantInUS() ? df_currency_convert(.5, 'USD', $c) : dfa([
			'AUD' => .5, 'CAD' => .5, 'CHF' => .5, 'DKK' => 2.5, 'EUR' => .5, 'GBP' => .3
			,'HKD' => 4, 'JPY' => 50, 'MXN' => 10, 'NOK' => 3, 'SEK' => 3, 'SGD' => .5, 'USD' => .5
		], $c, .5)
	;}
}