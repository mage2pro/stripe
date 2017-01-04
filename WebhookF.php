<?php
// 2017-01-04
namespace Dfe\Stripe;
class WebhookF extends \Df\StripeClone\WebhookF {
	/**             
	 * 2017-01-04       
	 * @override
	 * @see \Df\StripeClone\WebhookF::typeKey()
	 * @used-by \Df\StripeClone\WebhookF::_class()
	 * @return string
	 */
	final protected function typeKey() {return 'type';}
}


