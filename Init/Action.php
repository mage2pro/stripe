<?php
namespace Dfe\Stripe\Init;
use Df\Core\Exception as DFE;
use Dfe\Stripe\Facade\Source as fSource;
use Dfe\Stripe\Method as M;
use Dfe\Stripe\P\_3DS as p3DS;
use Dfe\Stripe\Settings as S;
use Magento\Sales\Model\Order as O;
use Stripe\Source as lSource;
use Stripe\StripeObject as lObject;
/**
 * 2017-11-06
 * @method M m()
 * @method S s()
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
	 * @throws DFE
	 */
	protected function redirectUrl() {
		/** @var string|null $r */
		if ($r = $this->need3DS()) {
			$source3DS = lSource::create(p3DS::p()); /** @var lSource $source3DS */
			/**
			 * 2017-11-06
			 * https://stripe.com/docs/api#source_object-redirect
			 * «Information related to the redirect flow.
			 * Present if the source is authenticated by a redirect (flow is redirect).»
			 * 2017-11-07
			 * A `redirect` value looks like:
			 *	{
			 *		"failure_reason": null,
			 *		"return_url": "https://mage2.pro/sandbox/dfe-stripe/customerReturn",
			 *		"status": "pending",
			 *		"url": "https://hooks.stripe.com/redirect/authenticate/src_1BLTIGFzKb8aMu..."
			 *	}
			 * @var lObject $redirect
			 */
			$redirect = $source3DS['redirect'];
			/**
			 * 2017-11-06
			 * «The failure reason for the redirect:
			 * 		`declined` (the authentication failed or the transaction was declined)
			 * 		`processing_error` (the redirect failed due to a technical error)
			 * 		`user_abort` (the customer aborted or dropped out of the redirect flow)
			 * Present only if the redirect status is `failed`.»
			 * https://stripe.com/docs/api#source_object-redirect-failure_reason
			 * @var string|null $fr
			 */
			if ($fr = $redirect['failure_reason']) {
				df_error(dftr($fr, [
					'declined' => 'The authentication failed or the transaction was declined.'
					,'processing_error' => 'The redirect failed due to a technical error.'
					,'user_abort' => 'The customer aborted or dropped out of the redirect flow.'
				]));
			}
			/**
			 * 2017-11-06
			 * «The status of the redirect, either:
			 * 		`failed` (failed authentication, cannot be reused)
			 * 		`not_required` (redirect should not be used)
			 * 		`pending` (ready to be used by your customer to authenticate the transaction)
			 * 		`succeeded` (succesful authentication, cannot be reused)
			 * » https://stripe.com/docs/api#source_object-redirect-status
			 * The status can not be `failed` here,
			 * because in this case `failure_reason` should be non-empty,
			 * and we have already handled it above.
			 *
			 * 2017-11-07
			 * Stripe API documentaion → «3D Secure Card Payments with Sources» →
			 * «Step 3: Create a 3D Secure Source object» → «Checking if verification is still required».
			 *
			 * *) When creating a 3D Secure source,
			 * its status is most commonly first set to `pending`
			 * and cannot yet be used to create a charge.
			 *
			 * *) In some cases, a 3D Secure source’s status can be immediately set to `chargeable`.
			 * This can happen if the customer’s card has not yet been enrolled in 3D Secure.
			 * Should this occur, the `redirect`.`status` value is set to `succeeded`
			 * and `three_d_secure`.`authenticated` set to `false`.
			 *
			 * *) The `status` attribute of the 3D Secure source can be immediately set to `failed`
			 * if the card does not support 3D Secure, or there was a technical failure
			 * (e.g., the card issuer’s 3D Secure service is down).
			 * Should this occur, you can either continue with a regular card payment,
			 * interrupt the payment flow, or attempt to create a 3D Secure source later.
			 * https://stripe.com/docs/sources/three-d-secure#checking-if-verification-is-still-required
			 */
			$r = 'pending' !== df_assert_ne('failed', $redirect['status']) ? null :
				/**
				 * 2017-11-06
				 * «The URL provided to you to redirect a customer to
				 * as part of a redirect authentication flow.»
				 * https://stripe.com/docs/api#source_object-redirect-url
				 */
				df_assert_sne($redirect['url'])
			;
		}
		return df_ftn($r);
	}

	/**
	 * 2017-11-07
	 * @used-by redirectUrl()
	 * @return bool
	 */
	private function need3DS() {
		/**
		 * 2017-11-06
		 * A customer can pay not only with a source, but with a saved card too,
		 * and $source with be acutally a card ID in this case, and it will have the «card_» prefix.
		 * @var string $sourceId
		 * @var bool $isSource
		 * @var string|null|bool $r
		 */
		if ($r = !!($isSource = df_starts_with($sourceId = fSource::trimmed(), 'src_'))) {
			$m = $this->m(); /** @var M $m */
			$s = $this->s(); /** @var S $s */
			$s->init();
			// 2017-11-07
			// "A response to «Retrieve a source» (`GET /v1/sources/src_<id>`)": https://mage2.pro/t/4884
			$source = lSource::retrieve($sourceId);	/** @var lSource $source */
			/**
			 * 2017-11-06
			 * Stripe API documentaion → «3D Secure Card Payments with Sources» →
			 * «Step 2: Determine if the card supports or requires 3D Secure».
			 * «The behavior of, and support for, 3D Secure can vary across card networks and types.
			 * *) For cards that are not supported, perform a regular card payment instead.
			 * *) Some card issuers, however, require the use of 3D Secure to reduce the risk for fraud,
			 * declining all charges that do not use this process.
			 * So you can best handle these different situations,
			 * check the `card`.`three_d_secure` attribute value of the card source
			 * before continuing with the 3D Secure process.
			 * 	`not_supported`:
			 * 		3D Secure is not supported on this card.
			 * 		Proceed with a regular card payment instead.
			 * 	`optional`:
			 * 		3D Secure is optional.
			 * 		The process isn’t required but can be performed to help reduce the likelihood of fraud.
			 * 	`required`:
			 * 		3D Secure is required.
			 * 		The process must be completed for a charge to be successful.
			 * »
			 * https://stripe.com/docs/sources/three-d-secure#check-requirement
			 */
			if ($card = $source['card']) {  /** @var array(string => mixed)|null $card */
				if ('not_supported' !== ($_3ds = $card['three_d_secure'])) { /** @var string $_3ds */
					$o = $m->o(); /** @var O $o */
					$r = ('required' === $_3ds) || $s->_3ds()->enable_(
						df_oq_sa($o, true)->getCountryId(), $o->getCustomerId()
					);
				}
			}
		}
		return df_ftn($r);
	}
}