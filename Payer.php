<?php
namespace Dfe\Stripe;
use Dfe\Stripe\Facade\Token as fToken;
# 2017-11-12
/** @used-by \Df\StripeClone\P\Charge::request() */
final class Payer extends \Df\StripeClone\Payer {
	/**
	 * 2017-11-12
	 * Note 1.
	 * Some Stripe's sources are single-use: https://stripe.com/docs/sources#single-use-or-reusable
	 * «Stripe API Documentation» → «Payment Methods Supported by the Sources API» → «Single-use or reusable»:
	 * «If a source can only be used once, this parameter is set to `single_use`
	 * and a source must be created each time a customer makes a payment.
	 * Such sources should not be attached to customers and should be charged directly instead.»
	 * Note 2. «Stripe API Reference» → «Sources» → «The source object» → `usage`:
	 * «Either `reusable` or `single_use`.
	 * Whether this source should be reusable or not.
	 * Some source types may or may not be reusable by construction,
	 * while other may leave the option at creation.
	 * If an incompatible value is passed, an error will be returned.»
	 * https://stripe.com/docs/api#source_object-usage
	 * @override
	 * @see \Df\StripeClone\Payer::tokenIsSingleUse()
	 * @used-by \Df\StripeClone\Payer::cardId()
	 * @used-by \Df\StripeClone\Payer::customerId()
	 */
	protected function tokenIsSingleUse():bool {return dfc($this, function() {return
		fToken::isPreviouslyUsedOrTrimmedSource($t = fToken::trimmed($this->token()))
		&& 'single_use' === dfe_stripe_source($t)['usage']
	;});}
}