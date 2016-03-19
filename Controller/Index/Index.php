<?php
namespace Dfe\Stripe\Controller\Index;
use Dfe\Stripe\Settings as S;
class Index extends \Magento\Framework\App\Action\Action {
	/**
	 * 2016-03-18
	 * @todo The controller is for ADVANCED integration:
	 * *) capturing and refunding payments through the Stripe interface
	 * (instead of the Magento interface),
	 * *) reverse notifications about the chargebacks and disputes.
	 * It will be inplemented next week in the version 1.1.
	 * @override
	 * @see \Magento\Framework\App\Action\Action::execute()
	 * @return \Magento\Framework\Controller\Result\Redirect
	 */
	public function execute() {return df_leh(function(){
		S::s()->init();
		/** @var array(string => mixed) $request */
		$request = df_json_decode(@file_get_contents('php://input'));
		// 2016-03-18
		// https://stripe.com/docs/api#event_object-type
		/** @var string $type */
		$type = $request['type'];
		switch ($type) {
			// 2016-03-18
			// https://stripe.com/docs/api#event_types-charge.captured
			case 'charge.captured':
				/** @var int $paymentId */
				//$paymentId = df_fetch_one('sales_payment_transaction', 'payment_id', [
				//	'txn_id' => dfa_deep($request, 'data/object/id')
				//]);
				break;
			// 2016-03-18
			// https://stripe.com/docs/api#event_types-charge.refunded
			case 'charge.refunded':
				/** @var array(string => string) $object */
				//$object = dfa_deep($request, 'data/object');
				/** @var string $charge */
				//$charge = $object['id'];
				/** @var string $amount */
				//$amount = $object['amount'];
				/** @var string $charge */
				//$amount_refunded = $object['amount_refunded'];
				break;
			// 2016-03-18
			// https://stripe.com/docs/api#event_types-charge.dispute.closed
			// Occurs when the dispute is closed
			// and the dispute status changes to charge_refunded, lost, warning_closed, or won.
			case 'charge.dispute.closed':
				break;
			// 2016-03-18
			// https://stripe.com/docs/api#event_types-charge.dispute.created
			// Occurs whenever a customer disputes a charge with their bank (chargeback).
			case 'charge.dispute.created':
				break;
			// 2016-03-18
			// https://stripe.com/docs/api#event_types-charge.dispute.funds_reinstated
			// Occurs when funds are reinstated to your account after a dispute is won.
			case 'charge.dispute.funds_reinstated':
				break;
			// 2016-03-18
			// https://stripe.com/docs/api#event_types-charge.dispute.funds_withdrawn
			// Occurs when funds are removed from your account due to a dispute.
			case 'charge.dispute.funds_withdrawn':
				break;
			// 2016-03-18
			// https://stripe.com/docs/api#event_types-charge.dispute.updated
			// Occurs when the dispute is updated (usually with evidence).
			case 'charge.dispute.updated':
				break;
		}
		return df_controller_json(['request' => $request]);
	});}
}
