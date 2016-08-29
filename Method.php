<?php
namespace Dfe\Stripe;
use Df\Core\Exception as DFE;
use Dfe\Stripe\Settings as S;
use Dfe\Stripe\Source\Action;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException as LE;
use Magento\Payment\Model\Info as I;
use Magento\Payment\Model\InfoInterface as II;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Creditmemo as CM;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment as OP;
use Magento\Sales\Model\Order\Payment\Transaction as T;
use Stripe\Error\Base as EStripe;
use Stripe\StripeObject;
class Method extends \Df\Payment\Method {
	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::acceptPayment()
	 * @param II|I|OP $payment
	 * @return bool
	 */
	public function acceptPayment(II $payment) {
		// 2016-03-15
		// Напрашивающееся $this->charge($payment) не совсем верно:
		// тогда не будет создан invoice.
		$payment->capture();
		return true;
	}

	/**
	 * 2016-03-07
	 * @override
	 * @see \Df\Payment\Method::canCapture()
	 * @return bool
	 */
	public function canCapture() {return true;}

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
	 * @see \Df\Payment\Method::canRefund()
	 * @return bool
	 */
	public function canRefund() {return true;}

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
	 * @see \Df\Payment\Method::canReviewPayment()
	 * @return bool
	 */
	public function canReviewPayment() {return true;}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::canVoid()
	 * @return bool
	 */
	public function canVoid() {return true;}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::denyPayment()
	 * @param II|I|OP  $payment
	 * @return bool
	 */
	public function denyPayment(II $payment) {return true;}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::getConfigPaymentAction()
	 * @return string
	 */
	public function getConfigPaymentAction() {
		return $this->isTheCustomerNew() ? S::s()->actionForNew() : S::s()->actionForReturned();
	}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::initialize()
	 * @param string $paymentAction
	 * @param object $stateObject
	 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Payment.php#L336-L346
	 * @see \Magento\Sales\Model\Order::isPaymentReview()
	 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order.php#L821-L832
	 * @return $this
	 */
	public function initialize($paymentAction, $stateObject) {
		$stateObject['state'] = O::STATE_PAYMENT_REVIEW;
		return $this;
	}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::isInitializeNeeded()
	 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Payment.php#L2336-L346
	 * @return bool
	 */
	public function isInitializeNeeded() {return Action::REVIEW === $this->getConfigPaymentAction();}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::refund()
	 * @param float|null $amount
	 * @return void
	 */
	protected function _refund($amount) {
		$this->api(function() use($amount) {
			/**
			 * 2016-03-17
			 * Метод @uses \Magento\Sales\Model\Order\Payment::getAuthorizationTransaction()
			 * необязательно возвращает транзакцию типа «авторизация»:
			 * в первую очередь он стремится вернуть родительскую транзакцию:
			 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Payment/Transaction/Manager.php#L31-L47
			 * Это как раз то, что нам нужно, ведь наш модуль может быть настроен сразу на capture,
			 * без предварительной транзакции типа «авторизация».
			 */
			/** @var T|false $tFirst */
			$tFirst = $this->ii()->getAuthorizationTransaction();
			if ($tFirst) {
				/** @var CM $cm */
				$cm = $this->ii()->getCreditmemo();
				/**
				 * 2016-03-24
				 * Credit Memo и Invoice отсутствуют в сценарии Authorize / Capture
				 * и присутствуют в сценарии Capture / Refund.
				 */
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
				/** @var string $firstId */
				$firstId = $this->transParentId($tFirst->getTxnId());
				// 2016-03-16
				// https://stripe.com/docs/api#create_refund
				/** @var \Stripe\Refund $refund */
				$refund = \Stripe\Refund::create(df_clean([
					// 2016-03-17
					// https://stripe.com/docs/api#create_refund-amount
					'amount' => !$amount ? null : self::amount($this->ii(), $amount)
					/**
					 * 2016-03-18
					 * Хитрый трюк,
					 * который позволяет нам не заниматься хранением идентификаторов платежей.
					 * Система уже хранит их в виде «ch_17q00rFzKb8aMux1YsSlBIlW-capture»,
					 * а нам нужно лишь отсечь суффиксы (Stripe не использует символ «-»).
					 */
					,'charge' => $firstId
					// 2016-03-17
					// https://stripe.com/docs/api#create_refund-metadata
					,'metadata' => $metadata
					// 2016-03-18
					// https://stripe.com/docs/api#create_refund-reason
					,'reason' => 'requested_by_customer'
				]));
				// 2016-08-20
				// Иначе автоматический идентификатор будет таким: <первичная транзакция>-capture-refund
				$this->ii()->setTransactionId($firstId . '-refund');
				$this->transInfo($refund);
			}
		});
	}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::_void()
	 * @return void
	 */
	protected function _void() {$this->_refund(null);}

	/**
	 * 2016-03-07
	 * @override
	 * @see https://stripe.com/docs/charges
	 * @see \Df\Payment\Method::capture()
	 * @param float $amount
	 * @param bool|null $capture [optional]
	 * @return void
	 * @throws \Stripe\Error\Card
	 */
	protected function charge($amount, $capture = true) {$this->api(function() use($amount, $capture) {
		/** @var T|false|null $auth */
		$auth = !$capture ? null : $this->ii()->getAuthorizationTransaction();
		if ($auth) {
			// 2016-03-17
			// https://stripe.com/docs/api#retrieve_charge
			/** @var \Stripe\Charge $charge */
			$charge = \Stripe\Charge::retrieve($auth->getTxnId());
			// 2016-03-17
			// https://stripe.com/docs/api#capture_charge
			$charge->capture();
			$this->transInfo($charge);
		}
		else {
			/** @var array(string => mixed) $params */
			$params = Charge::request($this->ii(), $this->iia(self::$TOKEN), $amount, $capture);
			/** @var \Stripe\Charge $charge */
			$charge = $this->api($params, function() use($params) {
				return \Stripe\Charge::create($params);
			});
			/**
			 * 2016-03-15
			 * Информация о банковской карте.
			 * https://stripe.com/docs/api#charge_object-source
			 * https://stripe.com/docs/api#card_object
			 */
			/** @var \Stripe\Card $card */
			$card = $charge->{'source'};
			$this->transInfo($charge, $params);
			/**
			 * 2016-03-15
			 * https://mage2.pro/t/941
			 * https://stripe.com/docs/api#card_object-last4
			 * «How is the \Magento\Sales\Model\Order\Payment's setCcLast4() / getCcLast4() used?»
			 */
			$this->ii()->setCcLast4($card->{'last4'});
			/**
			 * 2016-03-15
			 * https://stripe.com/docs/api#card_object-brand
			 */
			$this->ii()->setCcType($card->{'brand'});
			/**
			 * 2016-03-15
			 * Иначе операция «void» (отмена авторизации платежа) будет недоступна:
			 * «How is a payment authorization voiding implemented?»
			 * https://mage2.pro/t/938
			 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Payment.php#L540-L555
			 * @used-by \Magento\Sales\Model\Order\Payment::canVoid()
			 */
			$this->ii()->setTransactionId($charge->id);
			/**
			 * 2016-03-15
			 * Аналогично, иначе операция «void» (отмена авторизации платежа) будет недоступна:
			 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Payment.php#L540-L555
			 * @used-by \Magento\Sales\Model\Order\Payment::canVoid()
			 * Транзакция ситается завершённой, если явно не указать «false».
			 */
			$this->ii()->setIsTransactionClosed($capture);
		}
	});}

	/**
	 * 2016-05-03
	 * @override
	 * @see \Df\Payment\Method::iiaKeys()
	 * @used-by \Df\Payment\Method::assignData()
	 * @return string[]
	 */
	protected function iiaKeys() {return [self::$TOKEN];}

	/**
	 * 2016-08-20
	 * @override
	 * Хотя Stripe использует для страниц транзакций адреса вида
	 * https://dashboard.stripe.com/test/payments/<id>
	 * адрес без части «test» также успешно работает (даже в тестовом режиме).
	 * Использую именно такие адреса, потому что я не знаю,
	 * какова часть вместо «test» в промышленном режиме.
	 * @see \Df\Payment\Method::transUrl()
	 * @used-by \Df\Payment\Method::formatTransactionId()
	 * @param string $id
	 * @return string
	 */
	protected function transUrl($id) {
		return 'https://dashboard.stripe.com/payments/' . $this->transParentId($id);
	}

	/**
	 * 2016-03-17
	 * Чтобы система показала наше сообщение вместо общей фразы типа
	 * «We can't void the payment right now» надо вернуть объект именно класса
	 * @uses \Magento\Framework\Exception\LocalizedException
	 * https://mage2.pro/t/945
	 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Controller/Adminhtml/Order/VoidPayment.php#L20-L30
	 * @param array(callable|array(string => mixed)) ... $args
	 * @return mixed
	 * @throws Exception|LE
	 */
	private function api(...$args) {
		/** @var callable $function */
		/** @var array(string => mixed) $request */
		$args += [1 => []];
		list($function, $request) = is_callable($args[0]) ? $args : array_reverse($args);
		try {S::s()->init(); return $function();}
		catch (DFE $e) {throw $e;}
		catch (EStripe $e) {throw new Exception($e, $request);}
		catch (\Exception $e) {throw df_le($e);}
	}

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
		 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L32-L35
		 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::ADJUSTMENT_NEGATIVE
		 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L72-L75
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
				 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L112-L115
				 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::BASE_ADJUSTMENT_NEGATIVE
				 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L56-L59
				 */
				,"{$label} ({$iso3Base})" => $cm['base_' . $key]
			]
		);
	}

	/**
	 * 2016-08-20
	 * 2016-08-19
	 * Вообще говоря, можно получить уже готовую строку JSON
	 * кодом $response->getLastResponse()->body
	 * Однако в этой строке вложенность задаётся двумя пробелами,
	 * а я хочу, чтобы было 4, как у @see df_json_encode_pretty()
	 * @used-by \Dfe\Stripe\Method::_refund()
	 * @used-by \Dfe\Stripe\Method::charge()
	 * @param StripeObject $response
	 * @param array(string => mixed) $request [optional]
	 * @return void
	 */
	private function transInfo(StripeObject $response, array $request = []) {
		$this->iiaSetTR(array_map('df_json_encode_pretty', df_clean([
			Request::key() => $request, Response::key() => $response->getLastResponse()->json
		])));
	}

	/**
	 * 2016-08-20
	 * @used-by \Dfe\Stripe\Method::_refund()
	 * @used-by \Dfe\Stripe\Method::transUrl()
	 * @param string $childId
	 * @return string
	 */
	private function transParentId($childId) {return df_first(explode('-', $childId));}

	/**
	 * 2016-03-06
	 * 2016-08-23
	 * Отныне этот параметр может содержать не только токен новой карты
	 * (например: «tok_18lWSWFzKb8aMux1viSqpL5X»),
	 * но и идентификатор ранее использовавшейся карты
	 * (например: «card_18lGFRFzKb8aMux1Bmcjsa5L»).
	 * @var string
	 */
	private static $TOKEN = 'token';

	/**
	 * 2016-03-07
	 * https://stripe.com/docs/api/php#create_charge-amount
	 * «A positive integer in the smallest currency unit
	 * (e.g 100 cents to charge $1.00, or 1 to charge ¥1, a 0-decimal currency)
	 * representing how much to charge the card.
	 * The minimum amount is $0.50 (or equivalent in charge currency).»
	 *
	 * «Zero-decimal currencies»
	 * https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
	 * Here is the full list of zero-decimal currencies supported by Stripe:
	 * BIF: Burundian Franc
	 * CLP: Chilean Peso
	 * DJF: Djiboutian Franc
	 * GNF: Guinean FrancJ
	 * PY: Japanese Yen
	 * KMF: Comorian Franc
	 * KRW: South Korean Won
	 * MGA: Malagasy Ariary
	 * PYG: Paraguayan Guaraní
	 * RWF: Rwandan Franc
	 * VND: Vietnamese Đồng
	 * VUV: Vanuatu Vatu
	 * XAF: Central African Cfa Franc
	 * XOF: West African Cfa Franc
	 * XPF: Cfp Franc
	 *
	 * @param $payment II|I|OP
	 * @param float $amount
	 * @return int
	 */
	public static function amount(II $payment, $amount) {
		/** @var string[] $zeroDecimal */
		static $zeroDecimal = [
			'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA'
			,'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'
		];
		/** @var string $code */
		$code = $payment->getOrder()->getOrderCurrencyCode();
		return ceil($amount * (in_array($code, $zeroDecimal) ? 1 : 100));
	}
}