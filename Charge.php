<?php
namespace Dfe\Stripe;
use Df\Payment\Metadata;
use Dfe\Stripe\Settings as S;
use Magento\Payment\Model\Info as I;
use Magento\Payment\Model\InfoInterface as II;
use Magento\Sales\Model\Order\Payment as OP;
// 2016-07-02
class Charge extends \Df\Payment\Charge\WithToken {
	/**
	 * 2016-03-17
	 * @see https://stripe.com/docs/charges
	 * @return array(string => mixed)
	 */
	private function _request() {
		/**
		 * 2016-03-08
		 * Я так понимаю:
		 * *) invoice мы здесь получить не можем
		 * *) у order ещё нет id, но уже есть incrementId (потому что зарезервирован)
		 */
		/** @var Settings $s */
		$s = S::s();
		return [
			/**
			 * 2016-03-07
			 * https://stripe.com/docs/api/php#create_charge-amount
			 */
			'amount' => Method::amount($this->payment(), $this->amount())
			/**
			 * 2016-03-07
			 * «optional, default is true
			 * Whether or not to immediately capture the charge.
			 * When false, the charge issues an authorization (or pre-authorization),
			 * and will need to be captured later.
			 * Uncaptured charges expire in 7 days.
			 * For more information, see authorizing charges and settling later.»
			 */
			,'capture' => $this->needCapture()
			/**
			 * 2016-03-07
			 * https://stripe.com/docs/api/php#create_charge-currency
			 * «3-letter ISO code for currency.»
			 * https://support.stripe.com/questions/which-currencies-does-stripe-support
			 */
			,'currency' => $this->currencyCode()
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
			,'description' => $this->text($s->description())
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
			,'metadata' => Metadata::select($this->store(), $this->o(), $s->metadata())
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
			,'shipping' => $this->paramsShipping()
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
			,'source' => $this->token()
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
			,'statement_descriptor' => $s->statement()
		];
	}

	/** @return bool */
	private function needCapture() {return $this[self::$P__NEED_CAPTURE];}

	/**
	 * 2016-03-15
	 * @return array(string => mixed)
	 */
	private function paramsShipping() {
		/** @var \Magento\Sales\Model\Order\Address|null $sa */
		$sa = $this->addressSB();
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
			,'carrier' => df_order_shipping_title($this->o())
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
			,'tracking_number' => $this->o()['tracking_numbers']
		];
	}

	/**
	 * 2016-07-02
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__NEED_CAPTURE, RM_V_BOOL, false);
	}

	/** @var string */
	private static $P__NEED_CAPTURE = 'need_capture';

	/**
	 * 2016-07-02
	 * @used-by \Dfe\Stripe\Method::charge()
	 * @param II|I|OP $payment
	 * @param string $token
	 * @param float|null $amountBase [optional]
	 * @param bool $capture [optional]
	 * @return array(string => mixed)
	 */
	public static function request(II $payment, $token, $amountBase = null, $capture = true) {
		return (new self([
			self::$P__AMOUNT_BASE => $amountBase
			, self::$P__NEED_CAPTURE => $capture
			, self::$P__PAYMENT => $payment
			, self::$P__TOKEN => $token
		]))->_request();
	}
}