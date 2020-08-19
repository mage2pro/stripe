<?php
namespace Dfe\Stripe\Facade;
use Stripe\Card as lCard;
use Stripe\Customer as C;
# 2017-02-10
final class Customer extends \Df\StripeClone\Facade\Customer {
	/**
	 * 2017-02-10
	 * 2017-10-22
	 * Note 1.
	 * «Sources and Customers» → «Attaching a Source to an existing Customer»
	 * https://stripe.com/docs/sources/customers#attaching-a-source-to-an-existing-customer
	 * Note 2. A result looks like «src_1BFV8vFzKb8aMux1ooPxEEar».
	 * Note 3.
	 * A new source (which is not yet attached to a customer) has the «new_» prefix,
	 * which we added by the Dfe_Stripe/main::tokenFromResponse() method.
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::cardAdd()
	 * @used-by \Df\StripeClone\Payer::newCard()
	 * @param C $c
	 * @param string $token
	 * @return string
	 */
	function cardAdd($c, $token) {return $c->sources->create(['source' => Token::trimmed($token)])->id;}

	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::create()
	 * @used-by \Df\StripeClone\Payer::newCard()
	 * @param array(string => mixed) $p
	 * @return C
	 */
	function create(array $p) {return C::create($p);}

	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::id()
	 * @used-by \Df\StripeClone\Payer::newCard()
	 * @param C $c
	 * @return string
	 */
	function id($c) {return $c->id;}

	/**
	 * 2017-02-10
	 * «When requesting the ID of a customer that has been deleted,
	 * a subset of the customer’s information will be returned,
	 * including a deleted property, which will be true.»
	 * https://stripe.com/docs/api/php#retrieve_customer
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::_get()
	 * @used-by \Df\StripeClone\Facade\Customer::get()
	 * @param string $id
	 * @return C|null
	 */
	protected function _get($id) {/** @var C $c */return dfo($c = C::retrieve($id), 'deleted') ? null : $c;}

	/**
	 * 2017-02-11
	 * 2017-10-12
	 * `sources`:
	 * 		«The customer’s payment sources, if any.»
	 * 		https://stripe.com/docs/api#customer_object-sources
	 * `data`:
	 * 		«The list contains all payment sources that have been attached to the customer.»
	 * 		https://stripe.com/docs/api#customer_object-sources-data
	 * 2017-10-23
	 * Note 1.
	 * The result array really consists of lCard and @see \Stripe\Source instances,
	 * it is a Stripe's PHP SDK internal bevahior.
	 * Note 2.
	 * «`status`: The status of the source, one of
	 * `canceled`, `chargeable`, `consumed`, `failed`, or `pending`.
	 * Only chargeable sources can be used to create a charge.»
	 * https://stripe.com/docs/api#source_object-status
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::cardsData()
	 * @used-by \Df\StripeClone\Facade\Customer::cards()
	 * @param C $c
	 * @return array(lCard|\Stripe\Source)
	 * @see \Dfe\Stripe\Facade\Charge::cardData()
	 */
	protected function cardsData($c) {return array_filter($c->sources->{'data'}, function($o) {return
		$o instanceof lCard ||
			/**
			 * 2017-10-22
			 * «Stripe API Reference» → «Sources» → «The source object» → «status».
			 * «`status`: The status of the source, one of
			 * `canceled`, `chargeable`, `consumed`, `failed`, or `pending`.
			 * Only chargeable sources can be used to create a charge.»
			 * https://stripe.com/docs/api#source_object-status
			 */
			'chargeable' === $o['status']
			/**
			 * 2017-10-22
			 * «Stripe API Reference» → «Sources» → «The source object» → «type».
			 * «The type of the source.
			 * The `type` is a payment method, one of:
			 * 		`alipay`, `bancontact`, `card`, `giropay`, `ideal`, `sepa_debit`, `sofort`, `three_d_secure`
			 * An additional hash is included on the source with a name matching this value.
			 * It contains additional information specific to the payment method used.»
			 * https://stripe.com/docs/api#source_object-type
			 */
			&& 'card' === $o['type']
			/**
			 * 2017-10-22
			 * Note 1. «Stripe API Reference» → «Sources» → «The source object» → «usage».
			 * «Either `reusable` or `single_use`.
			 * Whether this source should be reusable or not.
			 * Some source types may or may not be reusable by construction,
			 * while other may leave the option at creation.»
			 * https://stripe.com/docs/api#source_object-usage
			 * String.
			 *
			 * Note 2. «Payment Methods Supported by the Sources API» → «Single-use or reusable».
			 * «Certain payment methods allow for the creation of sources
			 * that can be reused for additional payments
			 * without your customer needing to complete the payment process again.
			 * Sources that can be reused have their `usage` parameter set to `reusable`.
			 *
			 * Conversely, if a source can only be used once, this parameter is set to `single_use`
			 * and a source must be created each time a customer makes a payment.
			 * Such sources should not be attached to customers and should be charged directly instead.
			 * They can only be charged once and their status will transition to `consumed`
			 * when they get charged.
			 *
			 * Reusable sources must be attached to a `Customer` in order to be reused
			 * (they will get consumed as well if otherwise charged directly).
			 * Refer to the Sources & Customers guide to learn how to attach Sources to Customers
			 * and manage a Customer’s sources list.»
			 * https://stripe.com/docs/sources#single-use-or-reusable
			 * https://stripe.com/docs/sources/customers
			 */
			&& 'reusable' === $o['usage']
		;
	});}
}