<?php
namespace Dfe\Stripe\P;
// 2016-07-02
final class Charge extends \Df\StripeClone\P\Charge {
	/**
	 * 2017-02-11
	 * @override
	 * @see \Df\StripeClone\P\Charge::cardIdPrefix()
	 * @used-by \Df\StripeClone\P\Charge::usePreviousCard()
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
	 * @see \Df\StripeClone\P\Charge::p()
	 * @used-by \Df\StripeClone\P\Charge::request()
	 * @return array(string => mixed)
	 */
	protected function p() {return [
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
		,'shipping' => Address::p($forCharge = true)
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
	 * @see \Df\StripeClone\P\Charge::k_CardId()
	 * @used-by \Df\StripeClone\P\Charge::newCard()
	 * @used-by \Df\StripeClone\P\Charge::request()
	 * @return string
	 */
	protected function k_CardId() {return 'source';}

	/**
	 * 2017-02-18
	 * https://stripe.com/blog/dynamic-descriptors
	 * https://support.stripe.com/questions/does-stripe-support-dynamic-descriptors
	 * @override
	 * @see \Df\StripeClone\P\Charge::k_DSD()
	 * @used-by \Df\StripeClone\P\Charge::request()
	 * @return string
	 */
	protected function k_DSD() {return 'statement_descriptor';}
}