<?php
namespace Dfe\Stripe;
use Dfe\Stripe\Handler\DefaultT;
abstract class Handler extends \Df\Core\O {
	/**
	 * 2016-03-25
	 * @used-by \Dfe\Stripe\Handler::process()
	 * @return mixed
	 */
	abstract protected function process();

	/**
	 * 2016-03-25
	 * @param array(string => mixed) $request
	 * @return mixed
	 */
	public static function p(array $request) {
		// 2016-03-18
		// https://stripe.com/docs/api#event_object-type
		// Пример события с обоими разделителями: «charge.dispute.funds_reinstated»
		/** @var string $suffix */
		$suffix = df_implode_class('handler', df_explode_multiple(['.', '_'], $request['type']));
		$class = df_convention(__CLASS__, $suffix, DefaultT::class);
		/** @var Handler $i */
		$i = new $class($request);
		return $i->process();
	}
}