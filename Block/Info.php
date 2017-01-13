<?php
// 2016-08-20
namespace Dfe\Stripe\Block;
use Dfe\Stripe\Card;
use Dfe\Stripe\ResponseRecord;
/** @method \Dfe\Stripe\Method m() */
class Info extends \Df\Payment\Block\Info {
	/**
	 * 2016-08-20
	 * @override
	 * @see \Df\Payment\Block\Info::prepare()
	 * @used-by \Df\Payment\Block\Info::_prepareSpecificInformation()
	 */
	protected function prepare() {
		/** @var Card $c */
		$c = ResponseRecord::i($this->transF())->card();
		$this->siB('Stripe ID', $this->m()->formatTransactionId($this->transF()));
		$this->si($this->isBackend() ? 'Card Number' : 'Number', (string)$c);
		$this->siB(['Card Expires' => $c->expires(), 'Card Country' => $c->country()]);
	}
}