<?php
namespace Dfe\Stripe\Facade;
use Dfe\Stripe\Card;
use Stripe\Customer as C;
// 2017-02-10
final class Customer extends \Df\StripeClone\Facade\Customer {
	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::cardAdd()
	 * @used-by \Df\StripeClone\Charge::newCard()
	 * @param C $c
	 * @param string $token
	 * @return string
	 */
	public function cardAdd($c, $token) {return $c->sources->create(['source' => $token])->id;}

	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::cards()
	 * @used-by \Df\StripeClone\ConfigProvider::cards()
	 * @param C $c
	 * @return array(string => string)
	 */
	public function cards($c) {return array_map(function(\Stripe\Card $card) {return [
		'id' => $card->{'id'}, 'label' => (string)(new Card($card->__toArray()))
	];}, $c->sources->{'data'});}

	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::create()
	 * @used-by \Df\StripeClone\Charge::newCard()
	 * @param array(string => mixed) $p
	 * @return C
	 */
	public function create(array $p) {return C::create($p);}

	/**
	 * 2017-02-10
	 * «When requesting the ID of a customer that has been deleted,
	 * a subset of the customer’s information will be returned,
	 * including a deleted property, which will be true.»
	 * https://stripe.com/docs/api/php#retrieve_customer
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::get()
	 * @used-by \Df\StripeClone\Charge::newCard()
	 * @used-by \Df\StripeClone\ConfigProvider::cards()
	 * @param int $id
	 * @return C|null
	 */
	public function get($id) {
		/** @var C $c */
		$c = C::retrieve($id);
		return dfo($c, 'deleted') ? null : $c;
	}

	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Customer::id()
	 * @used-by \Df\StripeClone\Charge::newCard()
	 * @param C $c
	 * @return string
	 */
	public function id($c) {return $c->id;}
}