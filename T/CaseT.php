<?php
namespace Dfe\Stripe\T;
/**
 * 2017-10-19
 * @see \Dfe\Stripe\T\CaseT\CountrySpec
 * @method \Dfe\Stripe\Method m()
 */
abstract class CaseT extends \Df\Payment\TestCase {
	/**
	 * 2017-10-19
	 * @override
	 * @see \Df\Core\TestCase::setUp()
	 */
	protected function setUp() {parent::setUp(); $this->m()->s()->init();}
}