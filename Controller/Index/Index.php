<?php
namespace Dfe\Stripe\Controller\Index;
use Dfe\Stripe\Settings as S;
class Index extends \Magento\Framework\App\Action\Action {
	/**
	 * @override
	 * @see \Magento\Framework\App\Action\Action::execute()
	 * @return \Magento\Framework\Controller\Result\Redirect
	 */
	public function execute() {return df_leh(function(){
		S::s()->init();
		/** @var array(string => mixed) $request */
		$request = df_json_decode(@file_get_contents('php://input'));
		return df_controller_json($request);
	});}
}
