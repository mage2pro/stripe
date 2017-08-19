<?php
namespace Dfe\Stripe\P;
use Magento\Sales\Model\Order\Address as A;
// 2017-06-11
final class Address extends \Df\Payment\Operation {
	/**
	 * 2016-03-15
	 * @param bool $forCharge [optional]
	 * @return array(string => mixed)
	 */
	static function p($forCharge = false) {
		$i = new self(dfpm(__CLASS__)); /** @var self $i */
		/** @var A|null $a */ /** @var @var array(string => mixed) $shipping */
		return !($a = $i->addressS()) ? [] : [
			// 2016-03-14 hipping address.
			// https://stripe.com/docs/api/php#charge_object-shipping-address
			'address' => [
				// 2016-03-14 City/Suburb/Town/Village.
				// https://stripe.com/docs/api/php#charge_object-shipping-address-city
				'city' => $a->getCity()
				// 2016-03-14 2-letter country code
				// https://stripe.com/docs/api/php#charge_object-shipping-address-country
				,'country' => $a->getCountryId()
				// 2016-03-14 Address line 1 (Street address/PO Box/Company name)
				// https://stripe.com/docs/api/php#charge_object-shipping-address-line1
				,'line1' => $a->getStreetLine(1)
				// 2016-03-14 https://stripe.com/docs/api/php#charge_object-shipping-address-line2
				// Address line 2 (Apartment/Suite/Unit/Building)
				,'line2' => $a->getStreetLine(2)
				// 2016-03-14 Zip/Postal Code
				// https://stripe.com/docs/api/php#charge_object-shipping-address-postal_code
				,'postal_code' => $a->getPostcode()
				// 2016-03-14 State/Province/County
				// https://stripe.com/docs/api/php#charge_object-shipping-address-state
				,'state' => $a->getRegion()
			]
			// 2016-03-14 Recipient name.
			// https://stripe.com/docs/api/php#charge_object-shipping-name
			,'name' => $a->getName()
			// 2016-03-14 Recipient phone (including extension).
			// https://stripe.com/docs/api/php#charge_object-shipping-phone
			,'phone' => $a->getTelephone()
		] + (!$forCharge ? [] : [
			// 2016-03-14
			// The delivery service that shipped a physical product,
			// such as Fedex, UPS, USPS, etc.
			// https://stripe.com/docs/api/php#charge_object-shipping-carrier
			'carrier' => df_order_shipping_title($i->o())
			// 2016-03-14
			// The tracking number for a physical product,
			// obtained from the delivery service.
			// If multiple tracking numbers were generated for this purchase,
			// please separate them with commas.
			// https://stripe.com/docs/api/php#charge_object-shipping-tracking_number
			,'tracking_number' => $i->o()['tracking_numbers']
		]);
	}
}