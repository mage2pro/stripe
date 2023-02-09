<?php
namespace Dfe\Stripe\P;
use Dfe\Stripe\Facade\Token as fToken;
use Magento\Sales\Model\Order\Address as A;
# 2017-11-07
final class _3DS extends \Df\Payment\Charge {
	/**
	 * 2017-11-07
	 * @used-by \Dfe\Stripe\Init\Action::redirectUrl()
	 * @return array(string => mixed)
	 */
	static function p():array {$i = new self(dfpm(__CLASS__)); /** @var self $i */ return [
		'amount' => $i->amountF()
		,'currency' => $i->currencyC()
		,'redirect' => ['return_url' => $i->customerReturnRemote()]
		,'three_d_secure' => ['card' => fToken::trimmed()]
		,'type' => 'three_d_secure'
	];}
}