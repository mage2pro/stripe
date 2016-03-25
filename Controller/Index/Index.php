<?php
namespace Dfe\Stripe\Controller\Index;
use Dfe\Stripe\Handler;
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
		return df_controller_json(Handler::p(df_json_decode(@file_get_contents('php://input'))));
	});}
}
