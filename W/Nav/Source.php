<?php
namespace Dfe\Stripe\W\Nav;
# 2017-11-10
class Source extends \Df\StripeClone\W\Nav {
	/**
	 * 2017-11-10
	 * I return `null` here, because in the 3D Secure verification scenario
	 * the current transaction ID does not hinherit from the parent's one.
	 * The parent transaction ID is based on the Stripe's source ID, and looks like «src_<id>-3ds».
	 * The current transaction ID will be based the corresponding Stripe's charge,
	 * but at the time of the this method call, the charge is not yet executed,
	 * so we just do not know ID here, and will set it up later.
	 * Currently, this method is called only from @used-by \Df\Payment\W\Nav::op():
	 *		$result->setTransactionId($this->id());
	 * https://github.com/mage2pro/core/blob/3.2.37/Payment/W/Nav.php#L75-L125
	 * So we just need to call @see \Magento\Sales\Model\Order\Payment::setTransactionId() later.
	 * @override
	 * @see \Df\StripeClone\W\Nav::id()
	 * @used-by \Df\Payment\W\Handler::op()
	 * @return string|null
	 */
	protected function id() {return null;}
}


