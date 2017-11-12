<?php
namespace Dfe\Stripe;
use Df\Core\Exception as DFE;
use Df\Payment\Token;
use Df\StripeClone\Facade\Customer as fCustomer;
use \Dfe\Stripe\Facade\Token as fToken;
use Dfe\Stripe\Facade\Card;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Creditmemo as CM;
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
	 * 2017-10-12 It will be null for non-card payments (such payments are not implemented yet).
	 * @used-by \Dfe\Stripe\Currency::_iso3()
	 * @return string|null
	 */
	function cardType() {return dfc($this, function() {/** @var string $r */
		if (
			!($r = $this->iia(self::$II_CARD_TYPE))
			&& ($token = Token::get($this->ii(), false))
			&& fToken::isCard($token)
			&& ($customerId = df_ci_get($this))
		) {
			/**
			 * 2017-10-12
			 * A payment with a previously used card case.
			 * In this case we can detect the type of the previously used card
			 * by an additional Stripe API request:
			 * https://stripe.com/docs/api#retrieve_customer
			 * @see \Dfe\Stripe\Facade\Customer::cardsData()
			 * $token will be `null` in the non-payment scenarios.
			 * 2017-11-12
			 * The Stripe's API does not have a simple retrieve() method for a card like for a source.
			 * So our retrieval code is more complex.
			 * @var string|null $token
			 */
			$fc = fCustomer::s($this); /** @var FCustomer $fc */
			$this->s()->init();
			if ($customer = $fc->get($customerId) /** @var object|null $customer */) {
				/** @var Card|null $card */
				if ($card = df_find(function(Card $card) use($token) {return
					$token === $card->id()
				;}, $fc->cards($customer))) {
					$r = $card->brand();
				}
			}
		}
		return $r;
	});}

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
	 * 2017-10-12
	 * @override
	 * @see \Df\StripeClone\Method::iiaKeys()
	 * @used-by \Df\Payment\Method::assignData()
	 * @return string[]
	 */
	protected function iiaKeys() {return array_merge(parent::iiaKeys(), [self::$II_CARD_TYPE]);}

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
	 * 2017-04-15
	 * The «USD» currency could be not set up in the store,
	 * so use @uses df_currency_convert_safe() to get rid from a failure like «Undefined rate from "AUD-USD"».
	 *
	 * @used-by amountLimits()
	 * @param string $c
	 * @return float
	 */
	private function minimumAmount($c) {return
		$this->s()->isMerchantInUS() ? df_currency_convert_safe(.5, 'USD', $c) : dfa([
			'AUD' => .5, 'CAD' => .5, 'CHF' => .5, 'DKK' => 2.5, 'EUR' => .5, 'GBP' => .3
			,'HKD' => 4, 'JPY' => 50, 'MXN' => 10, 'NOK' => 3, 'SEK' => 3, 'SGD' => .5, 'USD' => .5
		], $c, .5)
	;}

	/**
	 * 2017-10-12
	 * @used-by cardType()
	 * @used-by iiaKeys()
	 */
	private static $II_CARD_TYPE = 'cardType';
}