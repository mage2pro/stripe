<?php
namespace Dfe\Stripe;
// 2016-08-24
class ApiCustomer extends \Stripe\Customer {
	/**
	 * 2016-08-24
	 * @return array(string => string)
	 */
	public function cards() {return array_map(function(\Stripe\Card $card) {return [
		'id' => $card->id, 'label' => Message::cardS($card->__toArray())
	];}, $this->sources->{'data'});}

	/**
	 * 2016-08-24
	 * «When requesting the ID of a customer that has been deleted,
	 * a subset of the customer’s information will be returned,
	 * including a deleted property, which will be true.»
	 * https://stripe.com/docs/api/php#retrieve_customer
	 *
	 * @used-by \Dfe\Stripe\Charge::apiCustomer()
	 * @used-by \Dfe\Stripe\ConfigProvider::savedCards()
	 *
	 * @return bool
	 */
	public function isDeleted() {return dfo($this, 'deleted');}

	/**
	 * 2016-08-24
	 * По умолчанию URL запроса вычисляется по имени класса.
	 * В нашем случае имя класса отличается от родительского,
	 * поэтому перекрываем этот метод.
	 * @override
	 * @see \Stripe\Customer::className()
	 * @used-by \Stripe\Customer::resourceUrl()
	 * @return string
	 */
	public static function className() {return 'customer';}
}