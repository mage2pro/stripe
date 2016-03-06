<?php
namespace Dfe\Stripe;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Info;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
class Method extends \Df\Payment\Method {
	/**
	 * 2016-03-06
	 * @override
	 * @see \Df\Payment\Method::assignData()
	 * @param DataObject $data
	 * @return $this
	 */
	public function assignData(DataObject $data) {
		parent::assignData($data);
		$this->iiaSet(self::$TOKEN, $data[self::$TOKEN]);
		return $this;
	}

	/**
	 * 2016-03-06
	 * @override
	 * How is a payment method's capture() used? https://mage2.pro/t/708
	 * @see https://stripe.com/docs/charges
	 * @see \Df\Payment\Method::capture()
	 * @param InfoInterface|Info|OrderPayment $payment
	 * @param float $amount
	 * @return $this
	 * @throws \Stripe\Error\Card
	 */
	public function capture(InfoInterface $payment, $amount) {
		// Set your secret key: remember to change this to your live secret key in production
		// See your keys here https://dashboard.stripe.com/account/apikeys
		\Stripe\Stripe::setApiKey(Settings::s()->secretKey($this->getStore()));
		// Create the charge on Stripe's servers - this will charge the user's card
		try {
			\Stripe\Charge::create([
				'amount' => 100 * $amount, // amount in cents, again
				'currency' => strtolower($payment->getOrder()->getBaseCurrencyCode()),
				'source' => $this->iia(self::$TOKEN),
				'description' => 'Example charge'
			]);
		} catch(\Stripe\Error\Card $e) {
			// The card has been declined
			throw $e;
		}
		return $this;
	}

	/**
	 * 2016-02-29
	 * @used-by Dfe/Stripe/etc/frontend/di.xml
	 * @used-by \Dfe\Stripe\ConfigProvider::getConfig()
	 */
	const CODE = 'dfe_stripe';
	/**
	 * 2016-03-06
	 * @var string
	 */
	private static $TOKEN = 'token';
}