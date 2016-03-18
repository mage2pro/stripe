<?php
namespace Dfe\Stripe;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException as LE;
use Magento\Payment\Model\Info;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Dfe\Stripe\Settings as S;
use Dfe\Stripe\Source\Action;
use Dfe\Stripe\Source\Metadata;
class Method extends \Df\Payment\Method {
	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::acceptPayment()
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @return bool
	 */
	public function acceptPayment(InfoInterface $payment) {
		// 2016-03-15
		// Напрашивающееся $this->charge($payment) не совсем верно:
		// тогда не будет создан invoice.
		$payment->capture();
		return true;
	}

	/**
	 * 2016-03-06
	 * @override
	 * @see \Df\Payment\Method::assignData()
	 * @param DataObject $data
	 * @return $this
	 */
	public function assignData(DataObject $data) {
		parent::assignData($data);
		$this->iiaSet(self::$TOKEN, $data[self::$TOKEN]);
		return $this;
	}

	/**
	 * 2016-03-07
	 * @override
	 * @see \Df\Payment\Method::::authorize()
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @param float $amount
	 * @return $this
	 */
	public function authorize(InfoInterface $payment, $amount) {
		return $this->charge($payment, $amount, $capture = false);
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
	 * 2016-03-06
	 * @override
	 * @see \Df\Payment\Method::capture()
	 * @see https://stripe.com/docs/charges
	 *
	 * $amount содержит значение в учётной валюте системы.
	 * https://github.com/magento/magento2/blob/6ce74b2/app/code/Magento/Sales/Model/Order/Payment/Operations/CaptureOperation.php#L37-L37
	 * https://github.com/magento/magento2/blob/6ce74b2/app/code/Magento/Sales/Model/Order/Payment/Operations/CaptureOperation.php#L76-L82
	 *
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @param float $amount
	 * @return $this
	 * @throws \Stripe\Error\Card
	 */
	public function capture(InfoInterface $payment, $amount) {
		$this->charge($payment, $amount);
		return $this;
	}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::denyPayment()
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @return bool
	 */
	public function denyPayment(InfoInterface $payment) {return true;}

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
	 * @see \Df\Payment\Method::getInfoBlockType()
	 * @return string
	 */
	public function getInfoBlockType() {return \Magento\Payment\Block\Info\Cc::class;}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::initialize()
	 * @param string $paymentAction
	 * @param object $stateObject
	 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Payment.php#L2336-L346
	 * @see \Magento\Sales\Model\Order::isPaymentReview()
	 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order.php#L821-L832
	 * @return $this
	 */
	public function initialize($paymentAction, $stateObject) {
		$stateObject['state'] = Order::STATE_PAYMENT_REVIEW;
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
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @param float $amount
	 * @return $this
	 */
	public function refund(InfoInterface $payment, $amount) {
		$this->_refund($payment, $amount);
		return $this;
	}

	/**
	 * 2016-03-08
	 * @override
	 * @see \Df\Payment\Method::setStore()
	 * @param int $storeId
	 * @return void
	 */
	public function setStore($storeId) {
		parent::setStore($storeId);
		S::s()->setScope($storeId);
	}

	/**
	 * 2016-03-15
	 * @override
	 * @see \Df\Payment\Method::void()
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @return $this
	 */
	public function void(InfoInterface $payment) {
		$this->_refund($payment);
		return $this;
	}

	/**
	 * 2016-03-17
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @param float|null $amount [optional]
	 * @return void
	 */
	private function _refund(InfoInterface $payment, $amount = null) {
		$this->api(function() use($payment, $amount) {
			/**
			 * 2016-03-17
			 * Метод @uses \Magento\Sales\Model\Order\Payment::getAuthorizationTransaction()
			 * необязательно возвращает транзакцию типа «авторизация»:
			 * в первую очередь он стремится вернуть родительскую транзакцию:
			 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Payment/Transaction/Manager.php#L31-L47
			 * Это как раз то, что нам нужно, ведь наш модуль может быть настроен сразу на capture,
			 * без предварительной транзакции типа «авторизация».
			 */
			/** @var Transaction|false $parent */
			$parent = $payment->getAuthorizationTransaction();
			if ($parent) {
				/** @var Creditmemo $cm */
				$cm = $payment->getCreditmemo();
				/** @var Invoice $invoice */
				$invoice = $cm->getInvoice();
				$metadata = df_clean([
					'Comment' => $payment->getCreditmemo()->getCustomerNote()
					,'Credit Memo' => $cm->getIncrementId()
					,'Invoice' => $invoice->getIncrementId()
				])
					+ $this->metaAdjustments($cm, 'positive')
					+ $this->metaAdjustments($cm, 'negative')
				;
				// 2016-03-16
				// https://stripe.com/docs/api#create_refund
				\Stripe\Refund::create(df_clean([
					// 2016-03-17
					// https://stripe.com/docs/api#create_refund-amount
					'amount' => !$amount ? null : self::amount($payment, $amount)
					/**
					 * 2016-03-18
					 * Хитрый трюк,
					 * который позволяет нам не ханиматься хранением идентификаторов платежей.
					 * Система уже хранит их в виде «ch_17q00rFzKb8aMux1YsSlBIlW-capture»,
					 * а нам нужно лишь отсечь суффиксы (Stripe не использует символ «-»).
					 */
					,'charge' => df_first(explode('-', $parent->getTxnId()))
					// 2016-03-17
					// https://stripe.com/docs/api#create_refund-metadata
					,'metadata' => $metadata
					// 2016-03-18
					// https://stripe.com/docs/api#create_refund-reason
					,'reason' => 'requested_by_customer'
				]));
			}
		});
	}

	/**
	 * 2016-03-17
	 * Чтобы система показала наше сообщение вместо общей фразы типа
	 * «We can't void the payment right now» надо вернуть объект именно класса
	 * @uses \Magento\Framework\Exception\LocalizedException
	 * https://mage2.pro/t/945
	 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Controller/Adminhtml/Order/VoidPayment.php#L20-L30
	 * @param callable $function
	 * @throws LE
	 */
	private function api($function) {
		df_leh(function() use($function) {S::s()->init(); $function();});
	}

	/**
	 * 2016-03-07
	 * @override
	 * @see https://stripe.com/docs/charges
	 * @see \Df\Payment\Method::capture()
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @param float|null $amount [optional]
	 * @param bool|null $capture [optional]
	 * @return $this
	 * @throws \Stripe\Error\Card
	 */
	private function charge(InfoInterface $payment, $amount = null, $capture = true) {
		$this->api(function() use($payment, $amount, $capture) {
			/** @var Transaction|false|null $auth */
			$auth = !$capture ? null : $payment->getAuthorizationTransaction();
			if ($auth) {
				// 2016-03-17
				// https://stripe.com/docs/api#retrieve_charge
				/** @var \Stripe\Charge $charge */
				$charge = \Stripe\Charge::retrieve($auth->getTxnId());
				// 2016-03-17
				// https://stripe.com/docs/api#capture_charge
				$charge->capture();
			}
			else {
				/** @var \Stripe\Charge $charge */
				$charge = \Stripe\Charge::create($this->paramsCharge($payment, $amount, $capture));
				/**
				 * 2016-03-15
				 * Информация о банковской карте.
				 * https://stripe.com/docs/api#charge_object-source
				 * https://stripe.com/docs/api#card_object
				 */
				/** @var \Stripe\Card $card */
				$card = $charge->{'source'};
				/**
				 * 2016-03-15
				 * https://mage2.pro/t/941
				 * https://stripe.com/docs/api#card_object-last4
				 * «How is the \Magento\Sales\Model\Order\Payment's setCcLast4() / getCcLast4() used?»
				 */
				$payment->setCcLast4($card->{'last4'});
				/**
				 * 2016-03-15
				 * https://stripe.com/docs/api#card_object-brand
				 */
				$payment->setCcType($card->{'brand'});
				/**
				 * 2016-03-15
				 * Иначе операция «void» (отмена авторизации платежа) будет недоступна:
				 * «How is a payment authorization voiding implemented?»
				 * https://mage2.pro/t/938
				 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Payment.php#L540-L555
				 * @used-by \Magento\Sales\Model\Order\Payment::canVoid()
				 */
				$payment->setTransactionId($charge->id);
				/**
				 * 2016-03-15
				 * Аналогично, иначе операция «void» (отмена авторизации платежа) будет недоступна:
				 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Payment.php#L540-L555
				 * @used-by \Magento\Sales\Model\Order\Payment::canVoid()
				 * Транзакция ситается завершённой, если явно не указать «false».
				 */
				$payment->setIsTransactionClosed($capture);
			}
		});
		return $this;
	}

	/**
	 * 2016-03-18
	 * @param Creditmemo $cm
	 * @param string $type
	 * @return array(string => float)
	 */
	private function metaAdjustments(Creditmemo $cm, $type) {
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
	 * 2016-03-17
	 * @see https://stripe.com/docs/charges
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @param float|null $amount [optional]
	 * @param bool|null $capture [optional]
	 * @return array(string => mixed)
	 */
	private function paramsCharge(InfoInterface $payment, $amount = null, $capture = true) {
		if (is_null($amount)) {
			$amount = $payment->getBaseAmountOrdered();
		}
		/**
		 * 2016-03-08
		 * Я так понимаю:
		 * *) invoice мы здесь получить не можем
		 * *) у order ещё нет id, но уже есть incrementId (потому что зарезервирован)
		 */
		/** @var \Magento\Sales\Model\Order $order */
		$order = $payment->getOrder();
		/** @var \Magento\Store\Model\Store $store */
		$store = $order->getStore();
		/** @var string $iso3 */
		$iso3 = $order->getBaseCurrencyCode();
		/** @var array(string => string) $vars */
		$vars = Metadata::vars($store, $order);
		return [
			/**
			 * 2016-03-07
			 * https://stripe.com/docs/api/php#create_charge-amount
			 */
			'amount' => self::amount($payment, $amount)
			/**
			 * 2016-03-07
			 * «optional, default is true
			 * Whether or not to immediately capture the charge.
			 * When false, the charge issues an authorization (or pre-authorization),
			 * and will need to be captured later.
			 * Uncaptured charges expire in 7 days.
			 * For more information, see authorizing charges and settling later.»
			 */
			,'capture' => $capture
			/**
			 * 2016-03-07
			 * https://stripe.com/docs/api/php#create_charge-currency
			 * «3-letter ISO code for currency.»
			 * https://support.stripe.com/questions/which-currencies-does-stripe-support
			 */
			,'currency' => $iso3
			/**
			 * 2016-03-07
			 * https://stripe.com/docs/api/php#create_charge-customer
			 * «The ID of an existing customer that will be charged in this request.»
			 *
			 * 2016-03-09
			 * Пустое значение передавать нельзя:
			 * «You have passed a blank string for 'customer'.
			 * You should remove the 'customer' parameter from your request or supply a non-blank value.»
			 */
			//,'customer' => ''
			/**
			 * 2016-03-07
			 * https://stripe.com/docs/api/php#create_charge-description
			 * «An arbitrary string which you can attach to a charge object.
			 * It is displayed when in the web interface alongside the charge.
			 * Note that if you use Stripe to send automatic email receipts to your customers,
			 * your receipt emails will include the description of the charge(s)
			 * that they are describing.»
			 *
			 * 2016-03-08
			 * Текст может иметь произвольную длину и не обрубается в интерфейсе Stripe.
			 * https://mage2.pro/t/903
			 */
			,'description' => df_var(S::s()->description(), $vars)
			/**
			 * 2016-03-07
			 * https://stripe.com/docs/api/php#create_charge-metadata
			 * «A set of key/value pairs that you can attach to a charge object.
			 * It can be useful for storing additional information about the customer
			 * in a structured format.
			 * It's often a good idea to store an email address in metadata for tracking later.»
			 *
			 * https://stripe.com/docs/api/php#metadata
			 * «You can have up to 20 keys, with key names up to 40 characters long
			 * and values up to 500 characters long.»
			 *
			 * 2016-03-08
			 * https://stripe.com/blog/adding-context-with-metadata
			 * «Adding context with metadata»
			 */
			,'metadata' => array_combine(
				dfa_select(Metadata::s()->map(), S::s()->metadata())
				,dfa_select($vars, S::s()->metadata())
			)
			/**
			 * 2016-03-07
			 * https://stripe.com/docs/api/php#create_charge-receipt_email
			 * «The email address to send this charge's receipt to.
			 * The receipt will not be sent until the charge is paid.
			 * If this charge is for a customer,
			 * the email address specified here will override the customer's email address.
			 * Receipts will not be sent for test mode charges.
			 * If receipt_email is specified for a charge in live mode,
			 * a receipt will be sent regardless of your email settings.»
			 */
			,'receipt_email' => null
			/**
			 * 2016-03-07
			 * «Shipping information for the charge.
			 * Helps prevent fraud on charges for physical goods.»
			 * https://stripe.com/docs/api/php#charge_object-shipping
			 */
			,'shipping' => $this->paramsShipping($payment)
			/**
			 * 2016-03-07
			 * https://stripe.com/docs/api/php#create_charge-source
			 * «A payment source to be charged, such as a credit card.
			 * If you also pass a customer ID,
			 * the source must be the ID of a source belonging to the customer.
			 * Otherwise, if you do not pass a customer ID,
			 * the source you provide must either be a token,
			 * like the ones returned by Stripe.js,
			 * or a associative array containing a user's credit card details,
			 * with the options described below.
			 * Although not all information is required, the extra info helps prevent fraud.»
			 */
			,'source' => $this->iia(self::$TOKEN)
			/**
			 * 2016-03-07
			 * «An arbitrary string to be displayed on your customer's credit card statement.
			 * This may be up to 22 characters.
			 * As an example, if your website is RunClub
			 * and the item you're charging for is a race ticket,
			 * you may want to specify a statement_descriptor of RunClub 5K race ticket.
			 * The statement description may not include <>"' characters,
			 * and will appear on your customer's statement in capital letters.
			 * Non-ASCII characters are automatically stripped.
			 * While most banks display this information consistently,
			 * some may display it incorrectly or not at all.»
			 */
			,'statement_descriptor' => S::s()->statement()
		];
	}

	/**
	 * 2016-03-15
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @return array(string => mixed)
	 */
	private function paramsShipping(InfoInterface $payment) {
		/** @var \Magento\Sales\Model\Order $order */
		$order = $payment->getOrder();
		/** @var \Magento\Sales\Model\Order\Address|null $ba */
		$sa = $order->getShippingAddress();
		/** @var @var array(string => mixed) $shipping */
		return !$sa ? [] : [
			// 2016-03-14
			// Shipping address.
			// https://stripe.com/docs/api/php#charge_object-shipping-address
			'address' => [
				// 2016-03-14
				// City/Suburb/Town/Village.
				// https://stripe.com/docs/api/php#charge_object-shipping-address-city
				'city' => $sa->getCity()
				// 2016-03-14
				// 2-letter country code
				// https://stripe.com/docs/api/php#charge_object-shipping-address-country
				,'country' => $sa->getCountryId()
				// 2016-03-14
				// Address line 1 (Street address/PO Box/Company name)
				// https://stripe.com/docs/api/php#charge_object-shipping-address-line1
				,'line1' => $sa->getStreetLine(1)
				// 2016-03-14
				// https://stripe.com/docs/api/php#charge_object-shipping-address-line2
				// Address line 2 (Apartment/Suite/Unit/Building)
				,'line2' => $sa->getStreetLine(2)
				// 2016-03-14
				// Zip/Postal Code
				// https://stripe.com/docs/api/php#charge_object-shipping-address-postal_code
				,'postal_code' => $sa->getPostcode()
				// 2016-03-14
				// State/Province/County
				// https://stripe.com/docs/api/php#charge_object-shipping-address-state
				,'state' => $sa->getRegion()
			]
			// 2016-03-14
			// The delivery service that shipped a physical product,
			// such as Fedex, UPS, USPS, etc.
			// https://stripe.com/docs/api/php#charge_object-shipping-carrier
			,'carrier' => df_order_shipping_title($order)
			// 2016-03-14
			// Recipient name.
			// https://stripe.com/docs/api/php#charge_object-shipping-name
			,'name' => $sa->getName()
			// 2016-03-14
			// Recipient phone (including extension).
			// https://stripe.com/docs/api/php#charge_object-shipping-phone
			,'phone' => $sa->getTelephone()
			// 2016-03-14
			// The tracking number for a physical product,
			// obtained from the delivery service.
			// If multiple tracking numbers were generated for this purchase,
			// please separate them with commas.
			// https://stripe.com/docs/api/php#charge_object-shipping-tracking_number
			,'tracking_number' => $order['tracking_numbers']
		];
	}

	/**
	 * 2016-02-29
	 * @used-by Dfe/Stripe/etc/frontend/di.xml
	 * @used-by \Dfe\Stripe\ConfigProvider::getConfig()
	 */
	const CODE = 'dfe_stripe';
	/**
	 * 2016-03-06
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
	 * @param $payment InfoInterface|Info|OrderPayment
	 * @param float $amount
	 * @return int
	 */
	private static function amount(InfoInterface $payment, $amount) {
		/** @var string[] $zeroDecimal */
		static $zeroDecimal = [
			'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA'
			,'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'
		];
		/** @var string $iso3 */
		$iso3 = $payment->getOrder()->getBaseCurrencyCode();
		return ceil($amount * (in_array($iso3, $zeroDecimal) ? 1 : 100));
	}
}