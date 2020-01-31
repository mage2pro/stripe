<?php
namespace Dfe\Stripe\Test\CaseT;
use Stripe\CountrySpec as lCountrySpec;
// 2017-10-19
// https://github.com/stripe/stripe-php/blob/v5.3.0/tests/CountrySpecTest.php
// «Stripe API Reference» → «Country Specs»: https://stripe.com/docs/api#country_specs
final class CountrySpec extends \Dfe\Stripe\Test\CaseT {
	/** @test 2017-10-19 */
	function t00() {}

	/**
	 * @test 2017-10-19
	 * https://stripe.com/docs/api#country_spec_object-supported_payment_methods
	 */
	function t01() {
		echo df_json_encode(lCountrySpec::retrieve('US')['supported_payment_methods']);
		echo df_json_encode(lCountrySpec::retrieve('NL')['supported_payment_methods']);
	}
}