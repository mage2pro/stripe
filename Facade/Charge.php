<?php
namespace Dfe\Stripe\Facade;
use Magento\Sales\Model\Order\Creditmemo as CM;
use Magento\Sales\Model\Order\Payment as OP;
use Stripe\Charge as C;
use Stripe\Refund as R;
// 2017-02-10
final class Charge extends \Df\StripeClone\Facade\Charge {
	/**
	 * 2017-02-10
	 * https://stripe.com/docs/api#retrieve_charge
	 * https://stripe.com/docs/api#capture_charge
	 * @override
	 * @see \Df\StripeClone\Facade\Charge::capturePreauthorized()
	 * @used-by \Df\StripeClone\Method::charge()
	 * @param string $id
	 * @param int|float $a
	 * The $a value is already converted to the PSP currency and formatted according to the PSP requirements.
	 * @return C
	 */
	function capturePreauthorized($id, $a) {return C::retrieve($id)->capture(['amount' => $a]);}

	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Charge::create()
	 * @used-by \Df\StripeClone\Method::chargeNew()
	 * @param array(string => mixed) $p
	 * @return C
	 */
	function create(array $p) {return C::create($p);}

	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Charge::id()
	 * @used-by \Df\StripeClone\Method::chargeNew()
	 * @param C $c
	 * @return string
	 */
	function id($c) {return $c->id;}

	/**
	 * 2017-10-22
	 * A new source (which is not yet attached to a customer) has the «new_» prefix,
	 * which we added by the Dfe_Stripe/main::tokenFromResponse() method.
	 * @override
	 * @see \Df\StripeClone\Facade\Charge::isCardId()
	 * @used-by \Df\StripeClone\Payer::usePreviousCard()
	 * @param string $id
	 * @return bool
	 */
	function isCardId($id) {return df_starts_with($id, ['card_', 'src_']);}

	/**
	 * 2017-02-12
	 * Returns the path to the bank card information
	 * in a charge converted to an array by @see \Dfe\Stripe\Facade\O::toArray()
	 * @override
	 * @see \Df\StripeClone\Facade\Charge::pathToCard()
	 * @used-by \Df\StripeClone\Block\Info::prepare()
	 * @used-by \Df\StripeClone\Facade\Charge::cardData()
	 * @return string
	 */
	function pathToCard() {return 'source';}

	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Charge::refund()
	 * @used-by void()
	 * @used-by \Df\StripeClone\Method::_refund()
	 * @param string $id
	 * @param float $a
	 * В формате и валюте платёжной системы.
	 * Значение готово для применения в запросе API.
	 * @return R
	 */
	function refund($id, $a) {return R::create(df_clean([
		// 2016-03-17 https://stripe.com/docs/api#create_refund-amount
		'amount' => $a
		// 2016-03-18
		// Хитрый трюк, который позволяет нам не заниматься хранением идентификаторов платежей.
		// Система уже хранит их в виде «ch_17q00rFzKb8aMux1YsSlBIlW-capture»,
		// а нам нужно лишь отсечь суффиксы (Stripe не использует символ «-»).
		,'charge' => $id
		// 2016-03-17 https://stripe.com/docs/api#create_refund-metadata
		,'metadata' => $this->refundMeta()
		// 2016-03-18 https://stripe.com/docs/api#create_refund-reason
		,'reason' => 'requested_by_customer'
	]));}

	/**
	 * 2017-02-10
	 * @override
	 * @see \Df\StripeClone\Facade\Charge::void()
	 * @used-by \Df\StripeClone\Method::_refund()
	 * @param string $id
	 * @return R
	 */
	function void($id) {return $this->refund($id, null);}

	/**
	 * 2016-03-18
	 * @used-by refundMeta()
	 * @param string $type
	 * @return array(string => float)
	 */
	private function refundAdjustments($type) {
		$cm = $this->cm(); /** @var CM $cm */
		$iso3Base = $cm->getBaseCurrencyCode(); /** @var string $iso3Base */
		$iso3 = $cm->getOrderCurrencyCode(); /** @var string $iso3 */
		$multiCurrency = $iso3Base !== $iso3; /** @var bool $multiCurrency */
		/**
		 * 2016-03-18
		 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::ADJUSTMENT_POSITIVE
		 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L32-L35
		 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::ADJUSTMENT_NEGATIVE
		 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L72-L75
		 */
		$key = "adjustment_$type"; /** @var string $key */
		$a = $cm[$key]; /** @var float $a */
		$label = ucfirst($type) . ' Adjustment'; /** @var string $label */
		return !$a ? [] : (
			!$multiCurrency
			? [$label => $a]
			: [
				"{$label} ({$iso3})" => $a
				/**
				 * 2016-03-18
				 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::BASE_ADJUSTMENT_POSITIVE
				 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L112-L115
				 * @uses \Magento\Sales\Api\Data\CreditmemoInterface::BASE_ADJUSTMENT_NEGATIVE
				 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Api/Data/CreditmemoInterface.php#L56-L59
				 */
				,"{$label} ({$iso3Base})" => $cm["base_$key"]
			]
		);
	}

	/**
	 * 2017-02-10
	 * Credit Memo и Invoice отсутствуют в сценарии Authorize / Capture
	 * и присутствуют в сценарии Capture / Refund.
	 * @used-by refund()
	 * @return array(string => mixed)
	 */
	private function refundMeta() {/** @var CM|null $cm */return !($cm = $this->cm()) ? [] :
		df_clean([
			'Comment' => $cm->getCustomerNote()
			,'Credit Memo' => $cm->getIncrementId()
			,'Invoice' => $cm->getInvoice()->getIncrementId()
		])
		+ $this->refundAdjustments('positive')
		+ $this->refundAdjustments('negative')
	;}
}