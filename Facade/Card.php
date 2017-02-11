<?php
namespace Dfe\Stripe\Facade;
use Stripe\Card as C;
// 2017-02-11
// https://stripe.com/docs/api#card_object
final class Card implements \Df\StripeClone\Facade\ICard {
	/**
	 * 2017-02-11
	 * @used-by \Df\StripeClone\Facade\Card::create()
	 * @param C|array(string => string) $p
	 */
	public function __construct($p) {$this->_p = is_array($p) ? $p : $p->__toArray();}

	/**
	 * 2017-02-11
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::brand()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @used-by \Df\StripeClone\CardFormatter::label()
	 * @return string
	 */
	public function brand() {return $this->_p['brand'];}

	/**
	 * 2017-02-11
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::country()
	 * @used-by \Df\StripeClone\CardFormatter::country()
	 * @return string
	 */
	public function country() {return $this->_p['country'];}

	/**
	 * 2017-02-11
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::expMonth()
	 * @used-by \Df\StripeClone\CardFormatter::exp()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @return string
	 */
	public function expMonth() {return $this->_p['exp_month'];}

	/**
	 * 2017-02-11
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::expYear()
	 * @used-by \Df\StripeClone\CardFormatter::exp()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @return string
	 */
	public function expYear() {return $this->_p['exp_year'];}

	/**
	 * 2017-02-11
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::id()
	 * @used-by \Df\StripeClone\ConfigProvider::cards()
	 * @used-by \Df\StripeClone\Facade\Customer::cardIdForJustCreated()
	 * @return string
	 */
	public function id() {return $this->_p['id'];}

	/**
	 * 2017-02-11
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::last4()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @used-by \Df\StripeClone\CardFormatter::label()
	 * @return string
	 */
	public function last4() {return $this->_p['last4'];}

	/**
	 * 2017-02-11
	 * @override
	 * @see \Df\StripeClone\Facade\ICard::owner()
	 * @used-by \Df\StripeClone\CardFormatter::ii()
	 * @return string
	 */
	public function owner() {return '';}

	/**
	 * 2017-02-11
	 * @var array(string => string)
	 */
	private $_p;
}