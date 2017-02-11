<?php
namespace Dfe\Stripe\Facade;
use Stripe\Refund as R;
// 2017-02-10
final class Refund extends \Df\StripeClone\Facade\Refund {
	/**
	 * 2017-02-10
	 * Метод должен вернуть идентификатор операции (не платежа!) в платёжной системе.
	 * Мы записываем его в БД и затем при обработке оповещений от платёжной системы
	 * смотрим, не было ли это оповещение инициировано нашей же операцией,
	 * и если было, то не обрабатываем его повторно.
	 *
	 * Структура @see \Stripe\Refund:
	 *	{
	 *		"id": "re_19deRAFzKb8aMux1eZEp32cX",
	 *		"object": "refund",
	 *		"amount": 269700,
	 *		"balance_transaction": "txn_19deRAFzKb8aMux1TLBWx6ZO",
	 *		"charge": "ch_19dePlFzKb8aMux1R0QUMP3T",
	 *		"created": 1484826640,
	 *		"currency": "thb",
	 *		"metadata": {
	 *			"Credit Memo": "RET-1-00030",
	 *			"Invoice": "INV-00121",
	 *			"Negative Adjustment (THB)": "359.6",
	 *			"Negative Adjustment (USD)": "10"
	 *		},
	 *		"reason": "requested_by_customer",
	 *		"receipt_number": null,
	 *		"status": "succeeded"
	 *	}
	 * Ключи ответа можно читать ещё так:
	 * $r['balance_transaction']
	 * $r->{'balance_transaction'}
	 *
	 * @override
	 * @see \Df\StripeClone\Facade\Refund::transId()
	 * @used-by \Df\StripeClone\Method::_refund()
	 * @param R $r
	 * @return string
	 * Пример результата: «txn_19deRAFzKb8aMux1TLBWx6ZO».
	 */
	function transId($r) {return $r->balance_transaction;}
}