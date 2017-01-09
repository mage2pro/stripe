<?php
namespace Dfe\Stripe;
use Df\Core\Exception as DFE;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Creditmemo as CM;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment as OP;
use Magento\Sales\Model\Order\Payment\Transaction as T;
use Stripe\Error\Base as EStripeLib;
use Stripe\StripeObject;
class Method extends \Df\StripeClone\Method {
	/**
	 * 2016-03-08
	 * @override
	 * @see \Df\Payment\Method::canCapturePartial()
	 * @return bool
	 */
	public function canCapturePartial() {return true;}

	/**
	 * 2016-03-08
	 * @override
	 * @see \Df\Payment\Method::canRefundPartialPerInvoice()
	 * @return bool
	 */
	public function canRefundPartialPerInvoice() {return true;}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::_refund()
	 * @used-by \Df\Payment\Method::refund()
	 * @param float|null $amount
	 * @return void
	 */
	final protected function _refund($amount) {$this->api(function() use($amount) {
		/**
		 * 2016-03-17
		 * Метод @uses \Magento\Sales\Model\Order\Payment::getAuthorizationTransaction()
		 * необязательно возвращает транзакцию типа «авторизация»:
		 * в первую очередь он стремится вернуть родительскую транзакцию:
		 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Model/Order/Payment/Transaction/Manager.php#L31-L47
		 * Это как раз то, что нам нужно, ведь наш модуль может быть настроен сразу на capture,
		 * без предварительной транзакции типа «авторизация».
		 */
		/** @var T|false $tFirst */
		$tFirst = $this->ii()->getAuthorizationTransaction();
		if ($tFirst) {
			/** @var CM|null $cm */
			$cm = $this->ii()->getCreditmemo();
			// 2016-03-24
			// Credit Memo и Invoice отсутствуют в сценарии Authorize / Capture
			// и присутствуют в сценарии Capture / Refund.
			if (!$cm) {
				$metadata = [];
			}
			else {
				/** @var Invoice $invoice */
				$invoice = $cm->getInvoice();
				$metadata = df_clean([
					'Comment' => $cm->getCustomerNote()
					,'Credit Memo' => $cm->getIncrementId()
					,'Invoice' => $invoice->getIncrementId()
				])
					+ $this->metaAdjustments($cm, 'positive')
					+ $this->metaAdjustments($cm, 'negative')
				;
			}
			/** @var string $chargeId */
			$chargeId = self::i2e($tFirst->getTxnId());
			// 2016-03-16
			// https://stripe.com/docs/api#create_refund
			/** @var \Stripe\Refund $refund */
			$refund = \Stripe\Refund::create(df_clean([
				// 2016-03-17
				// https://stripe.com/docs/api#create_refund-amount
				'amount' => !$amount ?: $this->amountFormat($amount)
				/**
				 * 2016-03-18
				 * Хитрый трюк,
				 * который позволяет нам не заниматься хранением идентификаторов платежей.
				 * Система уже хранит их в виде «ch_17q00rFzKb8aMux1YsSlBIlW-capture»,
				 * а нам нужно лишь отсечь суффиксы (Stripe не использует символ «-»).
				 */
				,'charge' => $chargeId
				// 2016-03-17
				// https://stripe.com/docs/api#create_refund-metadata
				,'metadata' => $metadata
				// 2016-03-18
				// https://stripe.com/docs/api#create_refund-reason
				,'reason' => 'requested_by_customer'
			]));
			// 2016-08-20
			// Иначе автоматический идентификатор будет таким: <первичная транзакция>-capture-refund
			$this->ii()->setTransactionId(self::e2i($chargeId, 'refund'));
			$this->transInfo($refund);
		}
	});}

	/**
	 * 2016-12-28
	 * @override
	 * @see \Df\StripeClone\Method::adaptException()
	 * @used-by \Df\StripeClone\Method::api()
	 * @param \Exception|EStripeLib $e
	 * @param array(string => mixed) $request [optional]
	 * @return \Exception
	 */
	final protected function adaptException(\Exception $e, array $request = []) {return
		$e instanceof EStripeLib ? new Exception($e, $request) : $e
	;}

	/**
	 * 2016-11-13
	 * https://stripe.com/docs/api/php#create_charge-amount
	 * https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
	 * @override
	 * @see \Df\Payment\Method::amountFactorTable()
	 * @used-by \Df\Payment\Method::amountFactor()
	 * @return int
	 */
	final protected function amountFactorTable() {return [
		1 => 'BIF,CLP,DJF,GNF,JPY,KMF,KRW,MGA,PYG,RWF,VND,VUV,XAF,XOF,XPF'
	];}

	/**
	 * 2016-12-28
	 * Информация о банковской карте.
	 * https://stripe.com/docs/api#charge_object-source
	 * https://stripe.com/docs/api#card_object
	 * https://stripe.com/docs/api#card_object-brand
	 * https://stripe.com/docs/api#card_object-last4
	 * @override
	 * @see \Df\StripeClone\Method::apiCardInfo()
	 * @used-by \Df\StripeClone\Method::chargeNew()
	 * @param \Stripe\Charge $charge
	 * @return array(string => string)
	 */
	final protected function apiCardInfo($charge) {
		/** @var \Stripe\Card $card */
		$card = $charge->{'source'};
		return [OP::CC_LAST_4 => $card->{'last4'}, OP::CC_TYPE => $card->{'brand'}];
	}

	/**
	 * 2016-12-28
	 * https://stripe.com/docs/api#retrieve_charge
	 * https://stripe.com/docs/api#capture_charge
	 * @override
	 * @see \Df\StripeClone\Method::apiChargeCapturePreauthorized()
	 * @used-by \Df\StripeClone\Method::charge()
	 * @param string $chargeId
	 * @return \Stripe\Charge
	 */
	final protected function apiChargeCapturePreauthorized($chargeId) {return
		\Stripe\Charge::retrieve($chargeId)->capture()
	;}

	/**
	 * 2016-12-28
	 * @override
	 * @see \Df\StripeClone\Method::apiChargeCreate()
	 * @used-by \Df\StripeClone\Method::chargeNew()
	 * @param array(string => mixed) $params
	 * @return \Stripe\Charge
	 */
	final protected function apiChargeCreate(array $params) {return \Stripe\Charge::create($params);}

	/**
	 * 2016-12-28
	 * @override
	 * @see \Df\StripeClone\Method::apiChargeId()
	 * @used-by \Df\StripeClone\Method::chargeNew()
	 * @param \Stripe\Charge $charge
	 * @return string
	 */
	final protected function apiChargeId($charge) {return $charge->id;}

	/**
	 * 2016-12-27
	 * @override
	 * @see \Df\StripeClone\Method::responseToArray()
	 * @used-by \Df\StripeClone\Method::transInfo()
	 * @param StripeObject $response
	 * @return array(string => mixed)
	 */
	final protected function responseToArray($response) {return $response->getLastResponse()->json;}

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
	final protected function transUrlBase(T $t) {return 'https://dashboard.stripe.com/payments';}

	/**
	 * 2016-03-18
	 * @param CM $cm
	 * @param string $type
	 * @return array(string => float)
	 */
	private function metaAdjustments(CM $cm, $type) {
		/** @var string $iso3Base */
		$iso3Base = $cm->getBaseCurrencyCode();
		/** @var string $iso3 */
		$iso3 = $cm->getOrderCurrencyCode();
		/** @var bool $multiCurrency */
		$multiCurrency = $iso3Base !== $iso3;
		/**
		 * 2016-03-18
		 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::ADJUSTMENT_POSITIVE
		 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L32-L35
		 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::ADJUSTMENT_NEGATIVE
		 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L72-L75
		 */
		/** @var string $key */
		$key = 'adjustment_' . $type;
		/** @var float $a */
		$a = $cm[$key];
		/** @var string $label */
		$label = ucfirst($type) . ' Adjustment';
		return !$a ? [] : (
			!$multiCurrency
			? [$label => $a]
			: [
				"{$label} ({$iso3})" => $a
				/**
				 * 2016-03-18
				 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::BASE_ADJUSTMENT_POSITIVE
				 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L112-L115
				 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::BASE_ADJUSTMENT_NEGATIVE
				 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L56-L59
				 */
				,"{$label} ({$iso3Base})" => $cm['base_' . $key]
			]
		);
	}
}