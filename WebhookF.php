<?php
// 2017-01-04
namespace Dfe\Stripe;
class WebhookF extends \Df\StripeClone\WebhookF {
	/**             
	 * 2017-01-04
	 * 2017-01-11
	 * Ключ «type» отсутствует в логе события на странице события в интерфейсе Stripe.
	 * Например: https://dashboard.stripe.com/test/events/evt_19aFW1FzKb8aMux1jQV7OZ9o
	 * Но реально этот ключ присутствует в запросе, и так было всегда с момента разработки модуля
	 * (март 2016).
	 * @override
	 * @see \Df\StripeClone\WebhookF::typeKey()
	 * @used-by \Df\StripeClone\WebhookF::_class()
	 * @return string
	 */
	final protected function typeKey() {return 'type';}
}


