<?php
namespace Dfe\Stripe\P;
use Dfe\Stripe\Facade\Token as fToken;
# 2017-06-11
final class Reg extends \Df\StripeClone\P\Reg {
	/**
	 * 2016-08-23
	 * @override
	 * @see \Df\StripeClone\P\Reg::p()
	 * @used-by \Df\StripeClone\P\Reg::request()
	 * @return array(string => mixed)
	 */
	protected function p():array {return [
		# 2016-08-22
		# https://stripe.com/docs/api/php#create_customer-account_balance
		# «An integer amount in cents
		# that is the starting account balance for your customer.
		# A negative amount represents a credit
		# that will be used before attempting any charges to the customer’s card;
		# a positive amount will be added to the next invoice.»
		'account_balance' => 0
		# 2016-08-22
		# https://stripe.com/docs/api/php#create_customer-business_vat_id
		# «The customer’s VAT identification number.
		# If you are using Relay, this field gets passed to tax provider
		# you are using for your orders.
		# This will be unset if you POST an empty value.
		# This can be unset by updating the value to null and then saving.»
		# 2018-09-22
		# «Received unknown parameter: business_vat_id»
		# https://github.com/mage2pro/stripe/issues/66
		# 2018-09-27
		# The `business_vat_id` field was changed from String to Hash called `tax_info`,
		# consisting of `tax_id` and `type`, in both requests and responses.
		# https://stripe.com/docs/upgrades?since=2017-08-15#whats-changed-since-2017-08-15
		//,'business_vat_id' => null
		# 2016-08-22
		# https://stripe.com/docs/api/php#create_customer-coupon
		# «If you provide a coupon code,
		# the customer will have a discount applied on all recurring charges.
		# Charges you create through the API will not have the discount.»
		# 2018-09-22
		# It looks like the «coupon» parameter has been removed from the Stripe's API too:
		# https://github.com/mage2pro/stripe/issues/70
		//,'coupon' => null
		# 2016-08-22
		# https://stripe.com/docs/api/php#create_customer-metadata
		# «A set of key/value pairs that you can attach to a customer object.
		# It can be useful for storing additional information about the customer
		# in a structured format. This will be unset if you POST an empty value.
		# This can be unset by updating the value to null and then saving.»
		,'metadata' => df_clean(['URL' => df_customer_backend_url($this->c())])
		# 2016-08-22
		# https://stripe.com/docs/api/php#create_customer-plan
		# «The identifier of the plan to subscribe the customer to.
		# If provided, the returned customer object will have a list of subscriptions
		# that the customer is currently subscribed to.
		# If you subscribe a customer to a plan without a free trial,
		# the customer must have a valid card as well.»
		# 2018-09-22
		# «You passed an empty string for 'plan'.
		# We assume empty values are an attempt to unset a parameter; however 'plan' cannot be unset.
		# You should remove 'plan' from your request or supply a non-empty value.»
		# https://github.com/mage2pro/stripe/issues/67
		//,'plan' => null
		# 2016-08-22
		# https://stripe.com/docs/api/php#create_customer-quantity
		# «The quantity you’d like to apply to the subscription you’re creating
		# (if you pass in a plan). For example, if your plan is 10 cents/user/month,
		# and your customer has 5 users, you could pass 5 as the quantity
		# to have the customer charged 50 cents (5 x 10 cents) monthly.
		# Defaults to 1 if not set. Only applies when the plan parameter is also provided.»
		# 2018-09-22
		# «Received unknown parameters: quantity, trial_end»
		# https://github.com/mage2pro/stripe/issues/68
		//,'quantity' => null
		/**
		 * 2017-08-30
		 * I have removed 'shipping' => Address::p() from here,
		 * because I set the `shipping` property for each charge individually:
		 * @see \Dfe\Stripe\P\Charge::p()
		 * It is especially important for the multishipping scenario.
		 * https://stripe.com/docs/api/php#create_customer-shipping
		 */
		# 2016-08-22
		# https://stripe.com/docs/api/php#create_customer-tax_percent
		# «A positive decimal (with at most two decimal places) between 1 and 100.
		# This represents the percentage of the subscription invoice subtotal
		# that will be calculated and added as tax to the final amount each billing period.
		# For example, a plan which charges $10/month with a tax_percent of 20.0
		# will charge $12 per invoice. Can only be used if a plan is provided.»
		# 2018-09-22
		# It looks like the «tax_percent» parameter has been removed from the Stripe's API too:
		# https://github.com/mage2pro/stripe/issues/69
		//,'tax_percent' => null
		# 2016-08-22
		# https://stripe.com/docs/api/php#create_customer-trial_end
		# «Unix timestamp representing the end of the trial period
		# the customer will get before being charged.
		# If set, trial_end will override the default trial period of the plan
		# the customer is being subscribed to.
		# The special value now can be provided to end the customer’s trial immediately.
		# Only applies when the plan parameter is also provided.»
		# 2018-09-22
		# «Received unknown parameters: quantity, trial_end»
		# https://github.com/mage2pro/stripe/issues/68
		//,'trial_end' => null
	];}

	/**
	 * 2017-10-22
	 * A new source (which is not yet attached to a customer) has the «new_» prefix,
	 * which we added by the Dfe_Stripe/main::tokenFromResponse() method.
	 * @override
	 * @see \Df\StripeClone\P\Reg::v_CardId()
	 * @used-by \Df\StripeClone\P\Reg::request()
	 * @return array(array(string => mixed))
	 */
	protected function v_CardId(string $id):array {return fToken::trimmed($id);}
}