<?php
namespace Dfe\Stripe;
// 2017-01-03
/** @see \Dfe\Stripe\Webhook\Charge */
abstract class Webhook extends \Df\StripeClone\Webhook {
	/**
	 * 2017-01-04
	 * Для Stripe-подобные платёжных систем
	 * наш внутренний идентификатор транзакции основывается на внешнем:
	 * <имя модуля>-<внешний идентификатор платежа>-<окончание типа события>.
	 * 2017-01-07
	 * Ключ должен быть именно «data/object/id».
	 * Ключ «id» у события тоже присутствует, но его значением является не идентификатор платежа
	 * («ch_*»), а идентификатор события («evt_*»).
	 * 2017-02-14
	 * [Stripe] An example of the «charge.captured» event (being sent to a webhook)
	 * https://mage2.pro/t/2745
	 * @override
	 * @see \Df\Payment\Webhook::parentIdRawKey()
	 * @used-by \Df\Payment\Webhook::parentIdRaw()
	 * @return string
	 */
	final protected function parentIdRawKey() {return 'data/object/id';}

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
	 * @see \Df\StripeClone\Webhook::roPath()
	 * @used-by \Df\StripeClone\Webhook::ro()
	 * @return string
	 */
	final protected function roPath() {return 'data/object';}
}