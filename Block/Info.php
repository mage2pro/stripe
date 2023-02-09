<?php
namespace Dfe\Stripe\Block;
use Dfe\Stripe\Method as M;
use Stripe\Source as lSource;
# 2017-11-12
/** @final Unable to use the PHP «final» keyword here because of the M2 code generation. */
class Info extends \Df\StripeClone\Block\Info {
	/**
	 * 2017-11-12
	 * @override
	 * @see \Df\StripeClone\Block\Info::cardData()
	 * @used-by \Df\StripeClone\Block\Info::card()
	 * @return lSource|array(string => mixed)
	 */
	protected function cardData() {
		/** @var array(string => mixed) $r */ /** @var string $initialSourceId */
		if (!($initialSourceId = $this->tm()->res0('three_d_secure/card'))) {
			$r = parent::cardData();
		}
		# 2017-10-12 It handles the already 3D Secure verified transactions.
		elseif ($responseF = $this->tm()->responseF()) {
			$r = $this->cardDataFromChargeResponse($responseF->r(M::IIA_TR_RESPONSE));
		}
		# 2017-10-12 It handles the 3D Secure unverified yet transactions.
		else {
			$this->s()->init();
			# 2017-11-12
			# "An initial reusable source for a card which requires a 3D Secure verification": https://mage2.pro/t/4893
			$r = dfe_stripe_source($initialSourceId);
		}
		return $r;
	}
}