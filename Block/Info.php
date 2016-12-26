<?php
// 2016-08-20
namespace Dfe\Stripe\Block;
use Dfe\Stripe\Message;
/** @method \Dfe\Stripe\Method m() */
class Info extends \Df\Payment\Block\Info {
	/**
	 * 2016-08-20
	 * @override
	 * @see \Df\Payment\Block\Info::prepare()
	 * @used-by \Df\Payment\Block\Info::_prepareSpecificInformation()
	 */
	protected function prepare() {
		/** @var Message $r */
		$r = Message::i($this->transF());
		$this->siB('Stripe ID', $this->m()->formatTransactionId($this->transF()));
		$this->si($this->isBackend() ? 'Card Number' : 'Number', $r->card());
		$this->siB(['Card Expires' => $r->expires(), 'Card Country' => $r->country()]);
	}
}