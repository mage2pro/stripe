<?php
namespace Dfe\Stripe\Init;
/**   
 * 2017-11-06
 * @method \Dfe\Stripe\Method m()
 * @method \Dfe\Stripe\Settings s()
 */
final class Action extends \Df\Payment\Init\Action {
	/**
	 * 2017-11-06
	 * Note 1.
	 * Some alternative Stripe payment options
	 * require the customer's redirection to the payment service provider.
	 * Note 2.
	 * «Certain payment methods require your customer
	 * to complete a particular action (flow) before the source is made chargeable.
	 * The type of flow that applies to a payment method
	 * is stated within the Source object’s `flow` parameter.
 	 *	`Redirect`:
	 * 		Your customer must approve the payment through a redirect
	 * 		(to their online banking service, as an example).
	 * 		When creating a source for this type of payment method,
	 * 		the Source object contains the URL to redirect your customer to.
	 * »
	 * https://stripe.com/docs/sources#flow-for-customer-action
	 * Note 3. 3D Secure verification.
	 * 3.1) First of all, I will implement such redirection for the 3D Secure verification.
	 * 3.2)
	 *  `url`:
	 * 		«The URL provided to you to redirect a customer to as part of a redirect authentication flow.»
	 * https://stripe.com/docs/api#source_object-redirect-url
	 * 3.3)
	 * «Once you determine if the card supports or requires 3D Secure,
	 * your customer must successfully verify their identity with their card issuer
	 * to make the source chargeable.
	 * To allow your customer to verify their identity using 3D Secure,
	 * redirect them to the URL provided within theredirect[url] attribute of the Source object.»
	 * https://stripe.com/docs/sources/three-d-secure#customer-action
	 * 3.4)
	 * We already have a similar 3D Secure verification flow for Omise:
	 * @see \Dfe\Omise\Init\Action::redirectUrl()
	 * https://github.com/mage2pro/omise/blob/1.11.1/Init/Action.php#L9-L22
	 *
	 * @override
	 * @see \Df\Payment\Init\Action::redirectUrl()
	 * @used-by \Df\Payment\Init\Action::action()
	 * @return string|null
	 */
	protected function redirectUrl() {return null;}
}