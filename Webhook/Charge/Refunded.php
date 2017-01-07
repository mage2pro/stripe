<?php
// 2017-01-04
namespace Dfe\Stripe\Webhook\Charge;
/**
 * 2016-09-08
 * Пример запроса:
	{
		"id": "evt_18rMfgFzKb8aMux1tzOgxsTz",
		"object": "event",
		"api_version": "2016-07-06",
		"created": 1473318604,
		"data": {
			"object": {
				"id": "ch_18rMf3FzKb8aMux1h2gjCzWm",
				"object": "charge",
				"amount": 61798,
				"amount_refunded": 10000,
				"application_fee": null,
				"balance_transaction": "txn_18rMfFFzKb8aMux1Kb0Hgv3y",
				"captured": true,
				"created": 1473318565,
				"currency": "ils",
				"customer": "cus_94SwL3Nnef6QCe",
				<...>
				"metadata": {
					"Customer Name": "Dmitry Fedyuk",
					"Order ID": "ORD-2016/09-00560",
					"Order Items": "New Very Prive, Alligator Briefcase",
					"Store Domain": "mage2.pro",
					"Store Name": "Mage2.PRO",
					"Store URL": "https://mage2.pro/sandbox/"
				},
				"order": null,
				"paid": true,
				"receipt_email": null,
				"receipt_number": null,
				"refunded": false,
				"refunds": {
					"object": "list",
					"data": [
						{
							"id": "re_18rMfgFzKb8aMux1MENpRk3T",
							"object": "refund",
							"amount": 10000,
							"balance_transaction": "txn_18rMfgFzKb8aMux1mrRYMXvT",
							"charge": "ch_18rMf3FzKb8aMux1h2gjCzWm",
							"created": 1473318604,
							"currency": "ils",
							"metadata": [],
							"reason": null,
							"receipt_number": null,
							"status": "succeeded"
						}
					],
					"has_more": false,
					"total_count": 1,
					"url": "/v1/charges/ch_18rMf3FzKb8aMux1h2gjCzWm/refunds"
				},
				"shipping": {
					<...>
				},
				"source": {
					<...>
				},
				"source_transfer": null,
				"statement_descriptor": "SAMPLE STATEMENT",
				"status": "succeeded"
			},
			"previous_attributes": {
				"amount_refunded": 0,
				"refunds": {
					"data": [],
					"total_count": 0
				}
			}
		},
		"livemode": false,
		"pending_webhooks": 1,
		"request": "req_99g12o0OJ7UKwA",
		"type": "charge.refunded"
	}
 */
class Refunded extends \Dfe\Stripe\Webhook\Charge {
	/**
	 * 2017-01-06
	 * @override
	 * @see \Df\StripeClone\Webhook::currentTransactionType()
	 * @used-by \Df\StripeClone\Webhook::id()
	 * @return string
	 */
	final protected function currentTransactionType() {return 'refund';}

	/**
	 * 2016-12-16
	 * @override
	 * @see \Dfe\Stripe\Webhook\Charge::parentTransactionType()
	 * @used-by \Dfe\Stripe\Webhook\Charge::adaptParentId()
	 * @return string
	 */
	final protected function parentTransactionType() {return 'capture';}
}