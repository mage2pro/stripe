<?php
namespace Dfe\Stripe;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Info;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Dfe\Stripe\Settings as S;
class Method extends \Df\Payment\Method {
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
	 * @param InfoInterface $payment
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
		return $this->charge($payment, $amount, $capture = true);
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
	 * 2016-03-07
	 * @override
	 * @see https://stripe.com/docs/charges
	 * @see \Df\Payment\Method::capture()
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @param float $amount
	 * @param bool $capture
	 * @return $this
	 * @throws \Stripe\Error\Card
	 */
	private function charge(InfoInterface $payment, $amount, $capture) {
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
		try {
			S::s()->init();
			\Stripe\Charge::create([
				/**
				 * 2016-03-07
				 * https://stripe.com/docs/api/php#create_charge-amount
				 */
				'amount' => self::convertAmountToCents($amount, $iso3)
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
				,'description' => df_var(S::s()->description(), [
					'customer.name' => df_order_customer_name($order)
					,'order.id' => $order->getIncrementId()
					,'order.items' => df_order_items($order)
					,'store.domain' => df_domain($store)
					, 'store.name' => $store->getFrontendName()
					, 'store.url' => $store->getBaseUrl()
				])
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
				,'metadata' => ['order' => $order->getIncrementId()]
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
				,'shipping' => []
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
				,'statement_descriptor' => null
			]);
		} catch(\Stripe\Error\Card $e) {
			// The card has been declined
			throw $e;
		}
		return $this;
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
	 * @param float $amount
	 * @param string $iso3
	 * @return int
	 */
	private static function convertAmountToCents($amount, $iso3) {
		static $zeroDecimal = [
			'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA'
			,'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'
		];
		return ceil($amount * (in_array($iso3, $zeroDecimal) ? 1 : 100));
	}
}