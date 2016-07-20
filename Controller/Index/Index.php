<?php
namespace Dfe\Stripe\Controller\Index;
use Df\Framework\Controller\Result\Json;
use Dfe\Stripe\Handler;
use Dfe\Stripe\Settings as S;
/**
 * 2016-03-18
 * The controller is for ADVANCED integration:
 * - capturing and refunding payments through the Stripe interface (instead of the Magento interface),
 * - reverse notifications about the chargebacks and disputes.
 *
 * 2016-03-27
 * Оказывается, аналогичная функциональность реализована в методе
 * @see \Magento\Paypal\Model\Ipn::_registerTransaction()
 * https://github.com/magento/magento2/blob/9546277/app/code/Magento/Paypal/Model/Ipn.php#L222-L278
 */
class Index extends \Magento\Framework\App\Action\Action {
	/**
	 * 2016-03-18
	 * @override
	 * @see \Magento\Framework\App\Action\Action::execute()
	 * @return Json
	 */
	public function execute() {return df_leh(function(){
		S::s()->init();
		return Json::i(Handler::p(df_json_decode(@file_get_contents($this->file()))));
	});}

	/**
	 * 2016-03-25
	 * @return string
	 */
	private function file() {
		return df_my_local() ? BP . '/_my/test/stripe/charge.refunded.json' : 'php://input';
	}
}
