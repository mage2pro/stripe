<?php
namespace Dfe\Stripe;
use Dfe\Stripe\Handler\DefaultT;
use Exception as E;
abstract class Handler extends \Df\Core\O {
	/**
	 * 2016-03-25
	 * @used-by \Dfe\Stripe\Handler::p()
	 * @return mixed
	 */
	abstract protected function process();

	/**
	 * 2016-03-28
	 * @used-by \Dfe\Stripe\Handler::p()
	 * @return bool
	 */
	protected function eligible() {return false;}

	/**
	 * 2016-03-25
	 * @param string|null $path [optional]
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
	 * 2016-05-11
	 * @return string
	 */
	protected function type() {return $this['type'];}

	/**
	 * 2016-03-25
	 * @param array(string => mixed) $request
	 * @return mixed
	 * @throws E
	 */
	public static function p(array $request) {
		/** @var mixed $result */
		try {
			// 2016-03-18
			// https://stripe.com/docs/api#event_object-type
			// Пример события с обоими разделителями: «charge.dispute.funds_reinstated»
			/** @var string $suffix */
			$suffix = df_implode_class('handler', df_explode_multiple(['.', '_'], $request['type']));
			$class = df_convention(__CLASS__, $suffix, DefaultT::class);
			/** @var Handler $i */
			$i = df_create($class, $request);
			$result = $i->eligible() ? $i->process() : 'The event is not for our store.';
		}
		catch (E $e) {
			/**
			 * 2016-03-27
			 * https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#5xx_Server_Error
			 * https://stripe.com/docs/webhooks#responding_to_a_webhook
			 * «To acknowledge receipt of a webhook, your endpoint should return a 2xx HTTP status code.
			 * Any response code outside the 2xx range
			 * will indicate to Stripe that you did not receive the webhook.
			 * When a webhook is not successfully received for any reason,
			 * Stripe will continue trying to send the webhook once an hour for up to 3 days.»
			 */
			df_response()->setStatusCode(500);
			if (df_my_local()) {
				// 2016-03-27
				// Удобно видеть стек на экране.
				throw $e;
			}
			else {
				$result = __($e->getMessage());
			}
		}
		return $result;
	}
}