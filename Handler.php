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
	 * @param string $path [optional]
	 * @return string|array(string => mixed)
	 */
	protected function o($path = null) {
		// 2016-03-25
		// null может быть ключом массива: https://3v4l.org/hWmWC
		if (!isset($this->{__METHOD__}[$path])) {
			/** @var string|mixed $result */
			$result = dfa_deep($this->_data, 'data/object');
			$this->{__METHOD__}[$path] = df_n_set(is_null($path) ? $result : dfa_deep($result, $path));
		}
		return df_n_get($this->{__METHOD__}[$path]);
	}

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