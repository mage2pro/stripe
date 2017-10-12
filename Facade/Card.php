<?php
namespace Dfe\Stripe\Facade;
use Stripe\Card as C;
// 2017-02-11 https://stripe.com/docs/api#card_object
final class Card implements \Df\StripeClone\Facade\ICard {
	/**
	 * 2017-02-11
	 * @used-by \Df\StripeClone\Facade\Card::create()
	 * @param C|array(string => string) $p
	 */
	function __construct($p) {$this->_p = is_array($p) ? $p : $p->__toArray();}

	/**
	 * 2017-02-11
	 * 2017-10-07 «Card brand.
	 * Can be Visa, American Express, MasterCard, Discover, JCB, Diners Club, or Unknown.»
	 * Type: string.
	 * https://stripe.com/docs/api#card_object-brand
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::brand()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @used-by \Df\StripeClone\CardFormatter::label()  
	 * @used-by \Dfe\Stripe\Method::cardType()
	 * @return string
	 */
	function brand() {return $this->_p['brand'];}

	/**
	 * 2017-02-11
	 * 2017-10-07
	 * Note 1. It should be an ISO-2 code or `null`.
	 * Note 2. «Two-letter ISO code representing the country of the card.
	 * You could use this attribute to get a sense of the international breakdown of cards you’ve collected.»
	 * Type: string.
	 * https://stripe.com/docs/api#card_object-country
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::country()
	 * @used-by \Df\StripeClone\CardFormatter::country()
	 * @return string
	 */
	function country() {return $this->_p['country'];}

	/**
	 * 2017-02-11
	 * 2017-10-07 «Two digit number representing the card’s expiration month»
	 * Type: integer.
	 * https://stripe.com/docs/api#card_object-exp_month
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::expMonth()
	 * @used-by \Df\StripeClone\CardFormatter::exp()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @return int
	 */
	function expMonth() {return $this->_p['exp_month'];}

	/**
	 * 2017-02-11
	 * 2017-10-07 «Four digit number representing the card’s expiration year»
	 * Type: integer.
	 * https://stripe.com/docs/api#card_object-exp_year
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::expYear()
	 * @used-by \Df\StripeClone\CardFormatter::exp()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @return int
	 */
	function expYear() {return $this->_p['exp_year'];}

	/**
	 * 2017-02-11
	 * 2017-10-07 «Unique identifier for the object»
	 * Type: string.
	 * E.g.: «card_1BANTX2eZvKYlo2CUYKSJMUT».
	 * https://stripe.com/docs/api#card_object-id
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::id()
	 * @used-by \Df\StripeClone\ConfigProvider::cards()
	 * @used-by \Df\StripeClone\Facade\Customer::cardIdForJustCreated()   
	 * @used-by \Dfe\Stripe\Method::cardType()
	 * @return string
	 */
	function id() {return $this->_p['id'];}

	/**
	 * 2017-02-11
	 * 2017-10-07 «The last 4 digits of the card»
	 * Type: string.
	 * https://stripe.com/docs/api#card_object-last4
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::last4()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @used-by \Df\StripeClone\CardFormatter::label()
	 * @return string
	 */
	function last4() {return $this->_p['last4'];}

	/**
	 * 2017-02-11
	 * 2017-02-16
	 * «Provide an ability to require the cardholder's name»: https://github.com/mage2pro/stripe/issues/2
	 * 2017-10-07 «Cardholder name»
	 * Type: string.
	 * https://stripe.com/docs/api#card_object-name
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::owner()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @return string
	 */
	function owner() {return $this->_p['name'];}

	/**
	 * 2017-02-11
	 * @var array(string => string)
	 */
	private $_p;
}