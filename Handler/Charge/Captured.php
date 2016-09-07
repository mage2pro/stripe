<?php
namespace Dfe\Stripe\Handler\Charge;
use Df\Sales\Model\Order as DfOrder;
use Df\Sales\Model\Order\Invoice as DfInvoice;
use Dfe\Stripe\Handler\Charge;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException as LE;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\InvoiceService;
// 2016-03-25
// https://stripe.com/docs/api#event_types-charge.captured
// Occurs whenever a previously uncaptured charge is captured.
class Captured extends Charge {
	/**
	 * 2016-03-25
	 * @override
	 * Делаем по аналогии с @see \Magento\Sales\Controller\Adminhtml\Order\Invoice\Save::execute()
	 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Controller/Adminhtml/Order/Invoice/Save.php#L102-L235
	 * How does the backend invoicing work? https://mage2.pro/t/933
	 * @see \Dfe\Stripe\Handler::_process()
	 * @used-by \Dfe\Stripe\Handler::process()
	 * @return mixed
	 * @throws LE
	 */
	protected function process() {
		if (!$this->order()->canInvoice()) {
			throw new LE(__('The order does not allow an invoice to be created.'));
		}
		$this->order()->setIsInProcess(true);
		$this->order()->setCustomerNoteNotify(true);
		/** @var Transaction $t */
		$t = df_db_transaction();
		$t->addObject($this->invoice());
		$t->addObject($this->order());
		$t->save();
		/** @var InvoiceSender $sender */
		$sender = df_o(InvoiceSender::class);
		$sender->send($this->invoice());
		return $this->payment()->getId();
	}

	/**
	 * 2016-03-26
	 * @return Invoice|DfInvoice
	 * @throws LE
	 */
	private function invoice() {return dfc($this, function() {
		/** @var InvoiceService $invoiceService */
		$invoiceService = df_o(InvoiceService::class);
		/** @var Invoice|DfInvoice $result */
		$result = $invoiceService->prepareInvoice($this->order());
		if (!$result) {
			throw new LE(__('We can\'t save the invoice right now.'));
		}
		if (!$result->getTotalQty()) {
			throw new LE(__('You can\'t create an invoice without products.'));
		}
		df_register('current_invoice', $result);
		/**
		 * 2016-03-26
		 * @used-by \Magento\Sales\Model\Order\Invoice::register()
		 * https://github.com/magento/magento2/blob/8fd3e8/app/code/Magento/Sales/Model/Order/Invoice.php#L599-L609
		 * Используем именно \Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE,
		 * а не \Magento\Sales\Model\Order\Invoice::CAPTURE_OFFINE,
		 * чтобы была создана транзакция capture.
		 */
		$result->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
		$result->register();
		return $result;
	});}
}