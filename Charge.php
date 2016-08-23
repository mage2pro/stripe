<?php
namespace Dfe\Stripe;
use Df\Core\Exception as DFE;
use Df\Payment\Metadata;
use Dfe\Stripe\Settings as S;
use Magento\Payment\Model\Info as I;
use Magento\Payment\Model\InfoInterface as II;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment as OP;
// 2016-07-02
class Charge extends \Df\Payment\Charge\WithToken {
	/**
	 * 2016-03-08
	 * Я так понимаю:
	 * *) invoice мы здесь получить не можем
	 * *) у order ещё нет id, но уже есть incrementId (потому что зарезервирован)
	 * 2016-03-17
	 * @see https://stripe.com/docs/charges
	 * @return array(string => mixed)
	 */
	private function _request() {/** @var Settings $s */ $s = S::s(); return [
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
		 * https://stripe.com/docs/api/php#create_charge-customer
		 * «The ID of an existing customer that will be charged in this request.»
		 *
		 * 2016-03-09
		 * Пустое значение передавать нельзя:
		 * «You have passed a blank string for 'customer'.
		 * You should remove the 'customer' parameter from your request or supply a non-blank value.»
		 */
		,'customer' => $this->customerId()
		/**
		 * 2016-03-07
		 * https://stripe.com/docs/api/php#create_charge-currency
		 * «3-letter ISO code for currency.»
		 * https://support.stripe.com/questions/which-currencies-does-stripe-support
		 */
		,'currency' => $this->currencyCode()
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
		,'shipping' => $this->paramsShipping($forCharge = true)
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
		,'source' => $this->cardId()
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
	] + df_clean([]);}

	/** @return string */
	private function cardId() {
		return $this->usePreviousCard() ? $this->token() : (
			$this->_newCard
				? $this->_newCard->id
				: $this->sCustomer()->sources->{'data'}[0]->id
		);
	}

	/**
	 * 2016-08-23
	 * @return string
	 */
	private function customerId() {
		/** @var string $result */
		$result = $this->savedCustomerId();
		if (!$result) {
			df_assert(!$this->usePreviousCard());
			$result = $this->sCustomer()->id;
		}
		return $result;
	}

	/** @return bool */
	private function needCapture() {return $this[self::$P__NEED_CAPTURE];}

	/**
	 * 2016-03-15
	 * @param bool $forCharge [optional]
	 * @return array(string => mixed)
	 */
	private function paramsShipping($forCharge = false) {
		/** @var Address|null $sa */
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
			// Recipient name.
			// https://stripe.com/docs/api/php#charge_object-shipping-name
			,'name' => $sa->getName()
			// 2016-03-14
			// Recipient phone (including extension).
			// https://stripe.com/docs/api/php#charge_object-shipping-phone
			,'phone' => $sa->getTelephone()
		] + (!$forCharge ? [] : [
			// 2016-03-14
			// The delivery service that shipped a physical product,
			// such as Fedex, UPS, USPS, etc.
			// https://stripe.com/docs/api/php#charge_object-shipping-carrier
			'carrier' => df_order_shipping_title($this->o())
			// 2016-03-14
			// The tracking number for a physical product,
			// obtained from the delivery service.
			// If multiple tracking numbers were generated for this purchase,
			// please separate them with commas.
			// https://stripe.com/docs/api/php#charge_object-shipping-tracking_number
			,'tracking_number' => $this->o()['tracking_numbers']
		]);
	}

	/**
	 * 2016-08-23
	 * Если покупатель был удалён в Stripe,
	 * то использовать его ранее сохранённую карту мы не можем.
	 * В принципе, в эту исключительную ситуацию мы практически не должны попадать,
	 * потому что для отображения покупателю списка его сохранённых карт
	 * мы запрашиваем этот список у Stripe в реальном времени:
	 * @see \Dfe\Stripe\ConfigProvider::savedCards()
	 * Получается, чтобы сюда попасть, мы должны были удалить покупателя
	 * уже после отображения страницы оформления заказа покупателю,
	 * но до завершения оформления заказа покупателем.
	 * @throws DFE
	 */
	private function rejectPreviousCard() {
		if ($this->usePreviousCard()) {
			df_error(
				'Sorry, your previous card data are unavailable. '
				. 'Please reenter the data again, or use another card.'
			);
		}
	}

	/**
	 * 2016-08-23
	 * Если $value равно null, то ключ удалится: @see dfo()
	 * @param string|null $value
	 * @return void
	 */
	private function saveCustomerId($value) {
		/** @var array(string => string) $info */
		$info = [self::CUSTOMER_INFO_KEY => $value];
		if (!$this->customer()) {
			df_checkout_session()->setDfCustomer($info);
		}
		else {
			df_customer_info_add($this->customer(), $info);
			/**
			 * 2016-08-22
			 * Сохранять покупателя надо обязательно,
			 * потому что при оформлении заказа зарегистрированным ранее покупателем
			 * его учётная запись не пересохраняется.
			 */
			df_eav_partial_save($this->customer());
		}
	}

	/**
	 * 2016-08-23
	 * @return string
	 */
	private function savedCustomerId() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_customer_info_get($this->customer(), Charge::CUSTOMER_INFO_KEY);
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-08-22
	 * Даже если покупатель в момент покупки ещё не имеет учётной записи в магазине,
	 * то всё равно разумно зарегистрировать его в Stripe и сохранить данные его карты,
	 * потому что Magento уже после оформления заказа предложит такому покупателю зарегистрироваться,
	 * и покупатель вполне может согласиться: https://mage2.pro/t/1967
	 *
	 * Если покупатель согласится создать учётную запись в магазине,
	 * то мы попадаем в @see \Df\Customer\Observer\CopyFieldset\OrderAddressToCustomer::execute()
	 * и там из сессии передаём данные в свежесозданную учётную запись.
	 *
	 * @return \Stripe\Customer
	 * @throws DFE
	 */
	private function sCustomer() {
		if (!isset($this->{__METHOD__})) {
			/** @var \Stripe\Customer|null $result */
			$result = null;
			if ($this->savedCustomerId()) {
				/**
				 * 2016-08-23
				 * https://stripe.com/docs/api/php#retrieve_customer
				 */
				$result = \Stripe\Customer::retrieve($this->savedCustomerId());
				/**
				 * 2016-08-23
				 * «When requesting the ID of a customer that has been deleted,
				 * a subset of the customer’s information will be returned,
				 * including a deleted property, which will be true.»
				 */
				if (dfo($result, 'deleted')) {
					$this->saveCustomerId(null);
					$result = null;
					$this->rejectPreviousCard();
				}
				/**
				 * 2016-08-23
				 * Покупатель уже зарегистрирован в Stripe,
				 * но он в этот раз хочет платить новой картой.
				 * Сохраняем её.
				 * https://stripe.com/docs/api#create_card
				 */
				if (!$this->usePreviousCard()) {
					$this->_newCard = $result->sources->create(['source' => $this->token()]);
				}
			}
			if (!$result) {
				$this->rejectPreviousCard();
				$result = \Stripe\Customer::create($this->sCustomerParams());
				$this->saveCustomerId($result->id);
			}
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-08-23
	 * @return array(string => mixed)
	 */
	private function sCustomerParams() {return [
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-account_balance
		 * «An integer amount in cents
		 * that is the starting account balance for your customer.
		 * A negative amount represents a credit
		 * that will be used before attempting any charges to the customer’s card;
		 * a positive amount will be added to the next invoice.»
		 */
		'account_balance' => 0
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-business_vat_id
		 * «The customer’s VAT identification number.
		 * If you are using Relay, this field gets passed to tax provider
		 * you are using for your orders.
		 * This will be unset if you POST an empty value.
		 * This can be unset by updating the value to null and then saving.»
		 */
		,'business_vat_id' => null
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-coupon
		 * «If you provide a coupon code,
		 * the customer will have a discount applied on all recurring charges.
		 * Charges you create through the API will not have the discount.»
		 */
		,'coupon' => null
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-description
		 * «An arbitrary string that you can attach to a customer object.
		 * It is displayed alongside the customer in the dashboard.
		 * This will be unset if you POST an empty value.
		 * This can be unset by updating the value to null and then saving.»
		 */
		,'description' => null
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-email
		 * «Customer’s email address.
		 * It’s displayed alongside the customer in your dashboard
		 * and can be useful for searching and tracking.
		 * This will be unset if you POST an empty value.
		 * This can be unset by updating the value to null and then saving.»
		 */
		,'email' => $this->o()->getCustomerEmail()
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-metadata
		 * «A set of key/value pairs that you can attach to a customer object.
		 * It can be useful for storing additional information about the customer
		 * in a structured format. This will be unset if you POST an empty value.
		 * This can be unset by updating the value to null and then saving.»
		 */
		,'metadata' => null
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-plan
		 * «The identifier of the plan to subscribe the customer to.
		 * If provided, the returned customer object will have a list of subscriptions
		 * that the customer is currently subscribed to.
		 * If you subscribe a customer to a plan without a free trial,
		 * the customer must have a valid card as well.»
		 */
		,'plan' => null
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-quantity
		 * «The quantity you’d like to apply to the subscription you’re creating
		 * (if you pass in a plan). For example, if your plan is 10 cents/user/month,
		 * and your customer has 5 users, you could pass 5 as the quantity
		 * to have the customer charged 50 cents (5 x 10 cents) monthly.
		 * Defaults to 1 if not set. Only applies when the plan parameter is also provided.»
		 */
		,'quantity' => null
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-shipping
		 * «optional associative array»
		 */
		,'shipping' => $this->paramsShipping()
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-source
		 * «The source can either be a token, like the ones returned by our Stripe.js,
		 * or a dictionary containing a user’s credit card details (with the options shown below).»
		 */
		,'source' => $this->token()
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-tax_percent
		 * «A positive decimal (with at most two decimal places) between 1 and 100.
		 * This represents the percentage of the subscription invoice subtotal
		 * that will be calculated and added as tax to the final amount each billing period.
		 * For example, a plan which charges $10/month with a tax_percent of 20.0
		 * will charge $12 per invoice. Can only be used if a plan is provided.»
		 */
		,'tax_percent' => null
		/**
		 * 2016-08-22
		 * https://stripe.com/docs/api/php#create_customer-trial_end
		 * «Unix timestamp representing the end of the trial period
		 * the customer will get before being charged.
		 * If set, trial_end will override the default trial period of the plan
		 * the customer is being subscribed to.
		 * The special value now can be provided to end the customer’s trial immediately.
		 * Only applies when the plan parameter is also provided.»
		 */
		,'trial_end' => null
	];}

	/**
	 * 2016-08-23
	 * Отныне параметр «token» может содержать не только токен новой карты
	 * (например: «tok_18lWSWFzKb8aMux1viSqpL5X»),
	 * но и идентификатор ранее использовавшейся карты
	 * (например: «card_18lGFRFzKb8aMux1Bmcjsa5L»).
	 * @return bool
	 */
	private function usePreviousCard() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_starts_with($this->token(), 'card_');
		}
		return $this->{__METHOD__};
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

	/**
	 * 2016-08-23
	 * Новая (только что зарегистрированная) карта ранее зарегистрированного в Stripe покупателя.
	 * @used-by \Dfe\Stripe\Charge::cardId()
	 * @used-by \Dfe\Stripe\Charge::sCustomer()
	 * @var \Stripe\Card|null
	 */
	private $_newCard;

	/**
	 * 2016-08-22
	 * @used-by \Dfe\Stripe\Charge::sCustomer()
	 * @used-by \Dfe\Stripe\Charge::cards()
	 */
	const CUSTOMER_INFO_KEY = 'stripe';

	/** @var string */
	private static $P__NEED_CAPTURE = 'need_capture';

	/**
	 * 2016-07-02
	 * @used-by \Dfe\Stripe\Method::charge()
	 * @param II|I|OP $payment
	 * @param string $token
	 * @param float|null $amount [optional]
	 * @param bool $capture [optional]
	 * @return array(string => mixed)
	 */
	public static function request(II $payment, $token, $amount = null, $capture = true) {
		return (new self([
			self::$P__AMOUNT => $amount
			, self::$P__NEED_CAPTURE => $capture
			, self::$P__PAYMENT => $payment
			, self::$P__TOKEN => $token
		]))->_request();
	}
}