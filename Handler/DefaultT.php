<?php
namespace Dfe\Stripe\Handler;
use Dfe\Stripe\Handler;
class DefaultT extends Handler {
	/**
	 * 2016-05-11
	 * Перкрываем метод, чтобы вернуть «Not implemented.» вместо «The event is not for our store.»
	 * @override
	 * @see \Dfe\Stripe\eligible::p()
	 * @used-by \Dfe\Stripe\Handler::p()
	 * @return bool
	 */
	protected function eligible() {return true;}

	/**
	 * 2016-03-25
	 * @override
	 * @see \Dfe\Stripe\Handler::_process()
	 * @used-by \Dfe\Stripe\Handler::process()
	 * @return mixed
	 */
	protected function process() {return "«{$this->type()}» event handling is not implemented.";}
}


