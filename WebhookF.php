<?php
// 2017-01-04
namespace Dfe\Stripe;
final class WebhookF extends \Df\StripeClone\WebhookF {
	/**             
	 * 2017-01-04
	 * 2017-01-11
	 * Ключ «type» отсутствует в логе события на странице события в интерфейсе Stripe.
	 * Например: https://dashboard.stripe.com/test/events/evt_19aFW1FzKb8aMux1jQV7OZ9o
	 * Но реально этот ключ присутствует в запросе, и так было всегда с момента разработки модуля
	 * (март 2016).
	 * 2017-02-14
	 * [Stripe] An example of the «charge.captured» event (being sent to a webhook)
	 * https://mage2.pro/t/2745
	 * @override
	 * @see \Df\StripeClone\WebhookF::typeKey()
	 * @used-by \Df\StripeClone\WebhookF::_class()
	 * @return string
	 */
	protected function typeKey() {return 'type';}
}


