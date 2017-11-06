<?php
namespace Dfe\Stripe;
use Df\Core\Exception as DFE;
use Stripe\Charge as lCharge;
// 2017-11-06
final class RedirectUrl {
	/**
	 * 2017-11-06
	 * @used-by \Dfe\Stripe\Init\Action::redirectUrl()
	 * @used-by \Dfe\Stripe\Method::redirectNeeded()
	 * @param lCharge|object $c
	 * @return string|false
	 * @throws DFE
	 */
	static function p(lCharge $c) {
		/**
		 * 2017-11-06
		 * https://stripe.com/docs/api#source_object-redirect
		 * «Information related to the redirect flow.
		 * Present if the source is authenticated by a redirect (flow is redirect).»
		 * @var array(string => mixed)|null $redirect
		 * @var string|null $r
		 */
		if ($r = !!($redirect = $c['redirect'])) {
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
			if ($fr = dfa($redirect, 'failure_reason')) {
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
			 */
			if ('pending' === df_assert_ne('failed', dfa($redirect, 'status'))) {
				/**
				 * 2017-11-06
				 * «The URL provided to you to redirect a customer to
				 * as part of a redirect authentication flow.»
				 * https://stripe.com/docs/api#source_object-redirect-url
				 */
				$r = df_assert_sne(dfa($redirect, 'url'));
			}
		}
		return $r;
	}
}