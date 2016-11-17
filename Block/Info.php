<?php
// 2016-08-20
namespace Dfe\Stripe\Block;
use Dfe\Stripe\Response as R;
/** @method \Dfe\Stripe\Method m() */
class Info extends \Df\Payment\Block\Info {
	/**
	 * 2016-08-20
	 * @override
	 * @see \Df\Payment\Block\Info::prepare()
	 * @used-by \Df\Payment\Block\Info::_prepareSpecificInformation()
	 */
	protected function prepare() {
		/** @var R $r */
		$r = R::i($this->transF());
		$this->siB('Stripe ID', $this->m()->formatTransactionId($this->transF()));
		$this->si($this->isBackend() ? 'Card Number' : 'Number', $r->card());
		$this->siB(['Card Expires' => $r->expires(), 'Card Country' => $r->country()]);
	}
}