<?php
namespace Dfe\Stripe;
use Magento\Sales\Model\Order\Address;
// 2016-07-02
final class Charge extends \Df\StripeClone\Charge {
	/**
	 * 2017-02-11
	 * @override
	 * @see \Df\StripeClone\Charge::cardIdPrefix()
	 * @used-by \Df\StripeClone\Charge::usePreviousCard()
	 * @return string
	 */
	protected function cardIdPrefix() {return 'card';}

	/**
	 * 2016-03-08
	 * Я так понимаю:
	 * *) invoice мы здесь получить не можем
	 * *) у order ещё нет id, но уже есть incrementId (потому что зарезервирован)
	 * 2016-03-17
	 * https://stripe.com/docs/charges
	 * @override
	 * @see \Df\StripeClone\Charge::pCharge()
	 * @used-by \Df\StripeClone\Charge::request()
	 * @return array(string => mixed)
	 */
	protected function pCharge() {return [
		// 2016-03-07
		// https://stripe.com/docs/api/php#create_charge-metadata
		// «A set of key/value pairs that you can attach to a charge object.
		// It can be useful for storing additional information about the customer
		// in a structured format.
		// It's often a good idea to store an email address in metadata for tracking later.»
		//
		// https://stripe.com/docs/api/php#metadata
		// «You can have up to 20 keys, with key names up to 40 characters long
		// and values up to 500 characters long.»
		//
		// 2016-03-08
		// https://stripe.com/blog/adding-context-with-metadata
		// «Adding context with metadata»
		'metadata' => $this->metadata(40, 500)
		// 2016-03-07
		// «The email address to send this charge's receipt to.
		// The receipt will not be sent until the charge is paid.
		// If this charge is for a customer,
		// the email address specified here will override the customer's email address.
		// Receipts will not be sent for test mode charges.
		// If receipt_email is specified for a charge in live mode,
		// a receipt will be sent regardless of your email settings.»
		// https://stripe.com/docs/api/php#create_charge-receipt_email
		,'receipt_email' => null
		// 2016-03-07
		// «Shipping information for the charge.
		// Helps prevent fraud on charges for physical goods.»
		// https://stripe.com/docs/api/php#charge_object-shipping
		,'shipping' => $this->pShipping($forCharge = true)
	];}	
	
	/**
	 * 2016-08-23
	 * @override
	 * @see \Df\StripeClone\Charge::pCustomer()
	 * @used-by \Df\StripeClone\Charge::newCard()
	 * @return array(string => mixed)
	 */
	protected function pCustomer() {return [
		// 2016-08-22
		// https://stripe.com/docs/api/php#create_customer-account_balance
		// «An integer amount in cents
		// that is the starting account balance for your customer.
		// A negative amount represents a credit
		// that will be used before attempting any charges to the customer’s card;
		// a positive amount will be added to the next invoice.»
		'account_balance' => 0
		// 2016-08-22
		// https://stripe.com/docs/api/php#create_customer-business_vat_id
		// «The customer’s VAT identification number.
		// If you are using Relay, this field gets passed to tax provider
		// you are using for your orders.
		// This will be unset if you POST an empty value.
		// This can be unset by updating the value to null and then saving.»
		,'business_vat_id' => null
		// 2016-08-22
		// https://stripe.com/docs/api/php#create_customer-coupon
		// «If you provide a coupon code,
		// the customer will have a discount applied on all recurring charges.
		// Charges you create through the API will not have the discount.»
		,'coupon' => null
		// 2016-08-22
		// https://stripe.com/docs/api/php#create_customer-metadata
		// «A set of key/value pairs that you can attach to a customer object.
		// It can be useful for storing additional information about the customer
		// in a structured format. This will be unset if you POST an empty value.
		// This can be unset by updating the value to null and then saving.»
		,'metadata' => df_clean(['URL' => df_customer_backend_url($this->c())])
		// 2016-08-22
		// https://stripe.com/docs/api/php#create_customer-plan
		// «The identifier of the plan to subscribe the customer to.
		// If provided, the returned customer object will have a list of subscriptions
		// that the customer is currently subscribed to.
		// If you subscribe a customer to a plan without a free trial,
		// the customer must have a valid card as well.»
		,'plan' => null
		// 2016-08-22
		// https://stripe.com/docs/api/php#create_customer-quantity
		// «The quantity you’d like to apply to the subscription you’re creating
		// (if you pass in a plan). For example, if your plan is 10 cents/user/month,
		// and your customer has 5 users, you could pass 5 as the quantity
		// to have the customer charged 50 cents (5 x 10 cents) monthly.
		// Defaults to 1 if not set. Only applies when the plan parameter is also provided.»
		,'quantity' => null
		// 2016-08-22
		// https://stripe.com/docs/api/php#create_customer-shipping
		// «optional associative array»
		,'shipping' => $this->pShipping()
		// 2016-08-22
		// https://stripe.com/docs/api/php#create_customer-tax_percent
		// «A positive decimal (with at most two decimal places) between 1 and 100.
		// This represents the percentage of the subscription invoice subtotal
		// that will be calculated and added as tax to the final amount each billing period.
		// For example, a plan which charges $10/month with a tax_percent of 20.0
		// will charge $12 per invoice. Can only be used if a plan is provided.»
		,'tax_percent' => null
		// 2016-08-22
		// https://stripe.com/docs/api/php#create_customer-trial_end
		// «Unix timestamp representing the end of the trial period
		// the customer will get before being charged.
		// If set, trial_end will override the default trial period of the plan
		// the customer is being subscribed to.
		// The special value now can be provided to end the customer’s trial immediately.
		// Only applies when the plan parameter is also provided.»
		,'trial_end' => null
	];}

	/**
	 * 2017-02-11
	 * 2017-02-18
	 * Ключ, значением которого является токен банковской карты.
	 * Этот ключ передаётся как параметр ДВУХ РАЗНЫХ запросов к API ПС:
	 * 1) в запросе на проведение транзакции (charge)
	 * 2) в запросе на сохранение банковской карты для будущего повторного использования
	 * У Stripe название этого параметра для обоих запросов совпадает.
	 * @override
	 * @see \Df\StripeClone\Charge::k_CardId()
	 * @used-by \Df\StripeClone\Charge::request()
	 * @used-by \Df\StripeClone\Charge::newCard()
	 * @return string
	 */
	protected function k_CardId() {return 'source';}

	/**
	 * 2017-02-18
	 * https://stripe.com/blog/dynamic-descriptors
	 * https://support.stripe.com/questions/does-stripe-support-dynamic-descriptors
	 * @override
	 * @see \Df\StripeClone\Charge::k_DSD()
	 * @used-by \Df\StripeClone\Charge::request()
	 * @return string
	 */
	protected function k_DSD() {return 'statement_descriptor';}

	/**
	 * 2016-03-15
	 * @param bool $forCharge [optional]
	 * @return array(string => mixed)
	 */
	private function pShipping($forCharge = false) {
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
}