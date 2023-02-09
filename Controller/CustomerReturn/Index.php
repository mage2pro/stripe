<?php
namespace Dfe\Stripe\Controller\CustomerReturn;
/**
 * 2017-11-07
 * Note 1.
 * Stripe API documentation → «3D Secure Card Payments with Sources» →
 * «Step 4: Have the customer complete 3D Secure verification».
 *
 * «Once you determine if the card supports or requires 3D Secure,
 * your customer must successfully verify their identity with their card issuer to make the source chargeable.
 * To allow your customer to verify their identity using 3D Secure,
 * redirect them to the URL provided within the `redirect[url]` attribute of the Source object.
 *
 * After the verification process, your customer is redirected back to the URL
 * provided as a value of redirect[return_url].
 * This happens regardless of whether verification was successful or not.
 *
 * If the customer has completed verification,
 * the Source object’s status is updated to `chargeable` and it is ready to use in a charge request.
 * If not, the status transitions to `failed`.
 *
 * Stripe populates the `redirect[return_url]` with the following GET parameters
 * when returning your customer to your website:
 *	 	`source`: a string representing the original ID of the Source object
 * 		`livemode`: indicates if this is a live payment, either true or false
 * 		`client_secret`:
 * 			used to confirm that the returning customer is the same one
 * 			who triggered the creation of the source (source IDs are not considered secret)
 *
 * You may include any other GET parameters you may need when specifying `redirect[return_url]`.
 * Do not use the above as parameter names yourself as these would be overridden with the values we populate.»
 * https://stripe.com/docs/sources/three-d-secure#customer-action
 *
 * 2017-11-08
 * It is called as https://site.com/dfe-stripe/customerReturn?client_secret=src_client_secret_<id>&livemode=false&source=src_<id>
 *
 * @final Unable to use the PHP «final» keyword here because of the M2 code generation.
 * @method \Dfe\Stripe\Settings s()
 */
class Index extends \Df\Payment\CustomerReturn {
	/**
	 * 2017-11-07
	 * Note 1.
	 * «Stripe populates the `redirect[return_url]` with the following GET parameters
	 * when returning your customer to your website:
	 *	 	`source`: a string representing the original ID of the Source object
	 * 		<...>
	 * »
	 * https://stripe.com/docs/sources/three-d-secure#customer-action
	 * Note 2.
	 * «The status of the source, one of `canceled`, `chargeable`, `consumed`, `failed`, or `pending`.
	 * Only `chargeable` sources can be used to create a charge.»
	 * https://stripe.com/docs/api/php#source_object-status
	 * Note 3.
	 * «If the customer has completed verification,
	 * the Source object’s status is updated to `chargeable` and it is ready to use in a charge request.
	 * If not, the status transitions to `failed`.»
	 * https://stripe.com/docs/sources/three-d-secure#customer-action
	 * Note 4.
	 * I treat the `pending` status as a successfull one because of this:
	 * «Stripe API documentation → «3D Secure Card Payments with Sources» → «Step 5: Charge the Source».
	 * Once the customer has authenticated the payment,
	 * the source’s `status` transitions to `chargeable` and it can be used to make a charge request.
	 * This transition happens asynchronously
	 * and may occur after the customer was redirected back to your website.
	 *
	 * Customers may also assume that the order process is complete
	 * once they have completed the 3D Secure authentication flow.
	 * This can result in the customers closing their browser
	 * instead of completing the redirection back to your app or website.
	 *
	 * For these reasons it is essential that your integration rely on webhooks
	 * to determine when the source becomes `chargeable` in order to create a charge.»
	 * https://stripe.com/docs/sources/three-d-secure#charge-request
	 * @override
	 * @see \Df\Payment\CustomerReturn::isSuccess()
	 * @used-by \Df\Payment\CustomerReturn::execute()
	 */
	final protected function isSuccess():bool {$this->s()->init(); return in_array(
		dfe_stripe_source(df_assert_sne(df_request('source')))['status'], ['chargeable', 'pending']
	);}
}