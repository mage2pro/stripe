<?php
namespace Dfe\Stripe\Controller\Index;
class Index extends \Magento\Framework\App\Action\Action {
	/**
	 * @override
	 * @see \Magento\Framework\App\Action\Action::execute()
	 * @return \Magento\Framework\Controller\Result\Redirect
	 */
	public function execute() {
		return df_controller_json(['ПРЕВЕД' => 'МЕДВЕД!']);
	}
}
