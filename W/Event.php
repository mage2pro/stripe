<?php
namespace Dfe\Stripe\W;
/**
 * 2017-03-15
 * @see \Dfe\Stripe\W\Event\Charge\Captured
 * @see \Dfe\Stripe\W\Event\Charge\Refunded
 * @see \Dfe\Stripe\W\Event\Source
 */
abstract class Event extends \Df\StripeClone\W\Event {
	/**
	 * 2017-01-04
	 * 2017-01-06
	 * Сообщение от платёжной системы — это иерархический JSON.
	 * На верхнем уровне иерархии расположены метаданные:
	 * *) тип сообщения (например: «charge.captured»).
	 * *) идентификатор платежа в платёжной системе
	 * *) тестовый ли платёж или промышленный
	 * *) версия API
	 * *) и.т.п.
	 * Конкретные данные сообщения расположены внутри иерархии по некоему пути.
	 * Этот путь и возвращает наш метод.
	 * 2017-02-14
	 * [Stripe] An example of the «charge.captured» event (being sent to a webhook)
	 * https://mage2.pro/t/2745
	 * @override
	 * @see \Df\StripeClone\W\Event::roPath()
	 * @used-by \Df\StripeClone\W\Event::k_pid()
	 * @used-by \Df\StripeClone\W\Event::ro()
	 */
	final protected function roPath():string {return 'data/object';}
}