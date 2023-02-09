<?php
namespace Dfe\Stripe\P;
# 2016-07-02
final class Charge extends \Df\StripeClone\P\Charge {
	/**
	 * 2017-02-11
	 * 2017-10-09 The key name of a bank card token or of a saved bank card ID.
	 * 2017-10-16
	 * Note 1. `source`:
	 * «A payment source to be charged, such as a credit card.
	 * If you also pass a customer ID,
	 * the source must be the ID of a source belonging to the customer (e.g., a saved card).
	 * Otherwise, if you do not pass a customer ID, the source you provide must either be a token,
	 * like the ones returned by Stripe.js, or a dictionary containing a user's credit card details,
	 * with the options described below.
	 * Although not all information is required, the extra info helps prevent fraud.»
	 * https://stripe.com/docs/api#create_charge-source
	 * Note 2.
	 * I always pass a saved card ID as the `source` parameter value.
	 * Other words, I always register a customer and its card (exchange a new card token to a saved card ID).
	 * 2017-11-12
	 * Despite of that the official documentation says (see above),
	 * the `source` value could be also a source ID.
	 * Moreover, such value is required in the 3D Secure verification scenario
	 * for bank cards, which require 3D Secure verification,
	 * because we can not attach such source to the customer:
	 * *) "A charge for the test card with required 3D Secure verification (4000000000003063) fails:
	 * «Your card was declined»": https://github.com/mage2pro/stripe/issues/46
	 * *) «Stripe API Documenation» → «3D Secure Card Payments with Sources» →
	 * «Step 5: Charge the Source» → «Make a charge request using the source».
	 * https://stripe.com/docs/sources/three-d-secure#make-a-charge-request-using-the-source
	 * @see \Dfe\Stripe\W\Strategy\Charge3DS::_handle():
	 * https://github.com/mage2pro/stripe/blob/c380b61a/W/Strategy/Charge3DS.php#L29-L43
	 * @override
	 * @see \Df\StripeClone\P\Charge::k_CardId()
	 * @used-by \Df\StripeClone\P\Charge::request()
	 * @used-by \Df\StripeClone\P\Reg::k_CardId()
	 */
	function k_CardId():string {return 'source';}

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
	protected function p():array {return [
		# 2016-03-07
		# https://stripe.com/docs/api/php#create_charge-metadata
		# «A set of key/value pairs that you can attach to a charge object.
		# It can be useful for storing additional information about the customer
		# in a structured format.
		# It's often a good idea to store an email address in metadata for tracking later.»
		#
		# https://stripe.com/docs/api/php#metadata
		# «You can have up to 20 keys, with key names up to 40 characters long
		# and values up to 500 characters long.»
		#
		# 2016-03-08
		# https://stripe.com/blog/adding-context-with-metadata
		# «Adding context with metadata»
		'metadata' => $this->metadata(40, 500)
		# 2016-03-07
		# «The email address to send this charge's receipt to.
		# The receipt will not be sent until the charge is paid.
		# If this charge is for a customer,
		# the email address specified here will override the customer's email address.
		# Receipts will not be sent for test mode charges.
		# If receipt_email is specified for a charge in live mode,
		# a receipt will be sent regardless of your email settings.»
		# https://stripe.com/docs/api/php#create_charge-receipt_email
		# 2018-09-27
		# 1) `receipt_email`: «email_invalid»
		# https://github.com/mage2pro/stripe/issues/72
		# 2) «When creating or updating a customer
		# the `email` parameter must contain an email address of valid shape.»
		# https://stripe.com/docs/upgrades?since=2017-08-15#whats-changed-since-2017-08-15
		//,'receipt_email' => null
		# 2016-03-07
		# «Shipping information for the charge.
		# Helps prevent fraud on charges for physical goods.»
		# https://stripe.com/docs/api/php#charge_object-shipping
		,'shipping' => Address::p()
	];}

	/**
	 * 2017-02-18
	 * https://stripe.com/blog/dynamic-descriptors
	 * https://support.stripe.com/questions/does-stripe-support-dynamic-descriptors
	 * @override
	 * @see \Df\StripeClone\P\Charge::k_DSD()
	 * @used-by \Df\StripeClone\P\Charge::request()
	 */
	protected function k_DSD():string {return 'statement_descriptor';}
}