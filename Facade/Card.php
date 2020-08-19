<?php
namespace Dfe\Stripe\Facade;
use Stripe\Card as lCard;
use Stripe\Source as lSource;
# 2017-02-11 https://stripe.com/docs/api#card_object
final class Card extends \Df\StripeClone\Facade\Card {
	/**
	 * 2017-02-11
	 * 2017-10-22
	 * We get an array here in the @used-by \Df\StripeClone\Block\Info::prepare().
	 * Otherwise, we get an object (lCard or lSource).
	 * @used-by \Df\StripeClone\Facade\Card::create()
	 * @param lCard|lSource|array(string => mixed) $p
	 */
	function __construct($p) {
		$p = dfe_stripe_a($p);
		/**
		 * 2017-11-12
		 * A derived single-use 3D Secure source does not contain the bank card details,
		 * so I retrieve the initial source.
		 * "A derived single-use 3D Secure source": https://mage2.pro/t/4894
		 * "An initial reusable source for a card which requires a 3D Secure verification":
		 * https://mage2.pro/t/4893
		 */
		/** @var string|null $initialSourceId */
		if ($initialSourceId = dfa_deep($p, 'three_d_secure/card')) {
			$p = dfe_stripe_a(dfe_stripe_source($initialSourceId));
		}
		$this->_p = Token::isCard($p['id']) ? $p : ['id' => $p['id']] + $p['card'] + $p['owner'];
	}

	/**
	 * 2017-02-11
	 * 2017-10-07 «Card brand.
	 * Can be Visa, American Express, MasterCard, Discover, JCB, Diners Club, or Unknown.»
	 * Type: string.
	 * https://stripe.com/docs/api#card_object-brand
	 * @override
	 * @see \Df\StripeClone\Facade\Card::brand()
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
	 * @see \Df\StripeClone\Facade\Card::country()
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
	 * @see \Df\StripeClone\Facade\Card::expMonth()
	 * @used-by \Df\StripeClone\CardFormatter::exp()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @used-by \Df\StripeClone\Facade\Card::isActive()
	 * @return int
	 */
	function expMonth() {return intval($this->_p['exp_month']);}

	/**
	 * 2017-02-11
	 * 2017-10-07 «Four digit number representing the card’s expiration year»
	 * Type: integer.
	 * https://stripe.com/docs/api#card_object-exp_year
	 * @override
	 * @see \Df\StripeClone\Facade\Card::expYear()
	 * @used-by \Df\StripeClone\CardFormatter::exp()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @used-by \Df\StripeClone\Facade\Card::isActive()
	 * @return int
	 */
	function expYear() {return intval($this->_p['exp_year']);}

	/**
	 * 2017-02-11
	 * 2017-10-07 «Unique identifier for the object»
	 * Type: string.
	 * E.g.: «card_1BANTX2eZvKYlo2CUYKSJMUT».
	 * https://stripe.com/docs/api#card_object-id
	 * @override
	 * @see \Df\StripeClone\Facade\Card::id()
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
	 * @see \Df\StripeClone\Facade\Card::last4()
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
	 * @see \Df\StripeClone\Facade\Card::owner()
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