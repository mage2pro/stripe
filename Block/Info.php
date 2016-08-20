<?php
namespace Dfe\Stripe\Block;
use Dfe\Stripe\Method;
use Dfe\Stripe\Request;
use Dfe\Stripe\Response;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Payment\Transaction as T;
/**
 * 2016-08-20
 * @method Method method()
 */
class Info extends \Df\Payment\Block\ConfigurableInfo {
	/**
	 * 2016-08-20
	 * @override
	 * @see \Magento\Payment\Block\ConfigurableInfo::_prepareSpecificInformation()
	 * @used-by \Magento\Payment\Block\Info::getSpecificInformation()
	 * @param DataObject|null $transport
	 * @return DataObject
	 */
	protected function _prepareSpecificInformation($transport = null) {
		/** @var DataObject $result */
		$result = parent::_prepareSpecificInformation($transport);
		if ($this->isBackend()) {
			$result['Stripe Transaction ID'] = $this->method()->formatTransactionId($this->res()->id());
		}
		$result[$this->isBackend() ? 'Card Number' : 'Number'] = $this->res()->card();
		if ($this->isBackend()) {
			$result->addData([
				'Card Expires' => $this->res()->expires()
				,'Card Country' => $this->res()->country()
			]);
		}
		$this->markTestMode($result);
		return $result;
	}

	/**
	 * 2016-08-20
	 * @return Request
	 */
	private function req() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = Request::i($this->transF());
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-08-20
	 * @return Response
	 */
	private function res() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = Response::i($this->transF());
		}
		return $this->{__METHOD__};
	}
}


