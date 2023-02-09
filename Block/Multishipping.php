<?php
namespace Dfe\Stripe\Block;
use Dfe\Stripe\ConfigProvider as CP;
use Dfe\Stripe\Method as M;
use Dfe\Stripe\Settings as S;
use Magento\Framework\View\Element\AbstractBlock as _P;
use Magento\Quote\Model\Quote\Address as A;
/**
 * 2017-08-25
 * 2017-08-26
 * This block is rendered here:  
 *		<!php if ($html = $block->getChildHtml('payment.method.' . $_code)) : !>
 *			<dd class="item-content">
 *				<!= $html !>
 *			</dd>
 *		<!php endif; !>
 * https://github.com/magento/magento2/blob/2.2.0-rc2.2/app/code/Magento/Multishipping/view/frontend/templates/checkout/billing.phtml#L50-L54
 * 2017-10-16
 * This block is instantiated by @used-by \Df\Payment\Method::getFormBlockType():
 *		final function getFormBlockType() {return df_con_hier(
 * 			$this, \Df\Payment\Block\Multishipping::class
 * 		);}
 * https://github.com/mage2pro/core/blob/3.2.3/Payment/Method.php#L953-L979
 * @final Unable to use the PHP «final» keyword here because of the M2 code generation.
 * @used-by \Df\Payment\Method::getFormBlockType()
 * @method \Dfe\Stripe\Method m()
 * @method \Dfe\Stripe\Settings s()
 */
class Multishipping extends \Df\Payment\Block\Multishipping {
 	/**
	 * 2017-08-25
	 * @override
	 * @see _P::_toHtml()
	 * @used-by _P::toHtml():
	 *		$html = $this->_loadCache();
	 *		if ($html === false) {
	 *			if ($this->hasData('translate_inline')) {
	 *				$this->inlineTranslation->suspend($this->getData('translate_inline'));
	 *			}
	 *			$this->_beforeToHtml();
	 *			$html = $this->_toHtml();
	 *			$this->_saveCache($html);
	 *			if ($this->hasData('translate_inline')) {
	 *				$this->inlineTranslation->resume();
	 *			}
	 *		}
	 *		$html = $this->_afterToHtml($html);
	 * https://github.com/magento/magento2/blob/2.2.0/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L643-L689
	 */
	final protected function _toHtml():string {
		$m = $this->m(); /** @var M $m */
		$s = $m->s(); /** @var S $s */
		$a = df_quote()->getBillingAddress(); /** @var A $a */
		return df_cc_n(df_tag('div',
			# 2017-08-26
			# The generic «.df-payment» selector is used here:
			# https://github.com/mage2pro/core/blob/2.10.43/Payment/view/frontend/web/main.less#L51
			['class' => df_cc_s('df-payment df-card', df_module_name_lc($m, '-'), 'df-singleLineMode')]
			+ df_widget($m, 'multishipping', CP::p() + ['sourceData' => $this->sourceData($a)])
			,df_block_output($m, 'multishipping', [
				/**
				 * 2017-10-18
				 * `The «Prefill the cardholder's name from the billing address?» option
				 * is wrongly ignored in the multi-shipping scenario`:
				 * https://github.com/mage2pro/stripe/issues/36
				 * I have implemented it similar to:
				 * https://github.com/mage2pro/core/blob/3.2.8/Payment/view/frontend/web/card.js#L171-L178
				 */
				'cardholder' => !$s->prefillCardholder() ? null : $this->cardholder($a)
				,'requireCardholder' => $s->requireCardholder()
			])
		), df_link_inline(df_asset_name('main', $m, 'css')));
	}

	/**
	 * 2017-10-18
	 * Note 1.
	 * `The ineligible characters should be automatically replaced by the corresponding eligible ones
	 * in the multi-shipping scenario while prefilling the cardholder's name
	 * (if «Prefill the cardholder's name from the billing address?» option is enabled)`:
	 * https://github.com/mage2pro/stripe/issues/37
	 * Note 2.
	 * I have implemented it for the single-shipping scenario in JavaScript:
	 * https://github.com/mage2pro/core/blob/3.2.9/Payment/view/frontend/web/card.js#L188-L201
	 *		baChange(this, function(a) {this.cardholder((a.firstname + ' ' + a.lastname).toUpperCase()
	 *			.normalize('NFD').replace(/[^\w\s-]/g, '')
	 *		);});
	 * https://github.com/mage2pro/core/issues/37#issuecomment-337546667
	 * Note 3.
	 * I have adapted an implementation from here:
	 * https://stackoverflow.com/questions/3371697#comment63507856_3371773
	 * @used-by self::_toHtml()
	 */
	private function cardholder(A $a):string {return df_translit(df_strtoupper(df_cc_s(
		$a->getFirstname(), $a->getLastname()
	)));}

	/**
	 * 2017-10-22
	 * @used-by self::sourceData()
	 * @return string
	 */
	private function pAddress(A $a) {return [
		/**
		 * 2017-10-20 «City/District/Suburb/Town/Village».
		 * «Stripe API Reference» → «Create a source» → «owner» → «address» → «city».
		 * https://stripe.com/docs/api#create_source-owner-address-city
		 * https://stripe.com/docs/api#source_object-owner-address-city
		 * String, optional.
		 */
		'city' => $a->getCity()
		/**
		 * 2017-10-20 «2-letter country code».
		 * «Stripe API Reference» → «Create a source» → «owner» → «address» → «country».
		 * https://stripe.com/docs/api#create_source-owner-address-country
		 * https://stripe.com/docs/api#source_object-owner-address-country
		 * String, optional.
		 */
		,'country' => $a->getCountryId()
		/**
		 * 2017-10-20 «Address line 1 (Street address/PO Box/Company name)».
		 * «Stripe API Reference» → «Create a source» → «owner» → «address» → «line1».
		 * https://stripe.com/docs/api#create_source-owner-address-line1
		 * https://stripe.com/docs/api#source_object-owner-address-line1
		 * String, optional.
		 */
		,'line1' => $a->getStreetLine(1)
		/**
		 * 2017-10-20 «Address line 2 (Apartment/Suite/Unit/Building)».
		 * «Stripe API Reference» → «Create a source» → «owner» → «address» → «line2».
		 * https://stripe.com/docs/api#create_source-owner-address-line2
		 * https://stripe.com/docs/api#source_object-owner-address-line2
		 * String, optional.
		 */
		,'line2' => $a->getStreetLine(2)
		/**
		 * 2017-10-20 «Zip/Postal Code».
		 * «Stripe API Reference» → «Create a source» → «owner» → «address» → «postal_code».
		 * https://stripe.com/docs/api#create_source-owner-address-postal_code
		 * https://stripe.com/docs/api#source_object-owner-address-postal_code
		 * String, optional.
		 */
		,'postal_code' => $a->getPostcode()
		/**
		 * 2017-10-20 «State/County/Province/Region».
		 * «Stripe API Reference» → «Create a source» → «owner» → «address» → «state».
		 * https://stripe.com/docs/api#create_source-owner-address-state
		 * https://stripe.com/docs/api#source_object-owner-address-state
		 * String, optional.
		 */
		,'state' => $a->getRegion()
	];}

	/**
	 * 2017-10-22
	 * @used-by self::_toHtml()
	 * @param A $a
	 * @return string
	 */
	private function sourceData(A $a) {return [
		/**
		 * 2017-10-20
		 * Note 1. «Stripe API Reference» → «Create a source» → «amount».
		 * «Amount associated with the source.
		 * This is the amount for which the source will be chargeable once ready.»
		 * https://stripe.com/docs/api#create_source-amount
		 * Integer, optional. Required for `single_use` sources.
		 * Note 2. «Payment Methods Supported by the Sources API» → «Single-use or reusable».
		 * https://stripe.com/docs/sources#single-use-or-reusable
		 */
		'amount' => null
		/**
		 * 2017-10-20 «Stripe API Reference» → «Create a source» → «currency».
		 * «Three-letter ISO code for the currency associated with the source.
		 * This is the currency for which the source will be chargeable once ready.»
		 * https://stripe.com/docs/api#create_source-currency
		 * https://stripe.com/docs/currencies#presentment-currencies
		 * Currency, optional.
		 */
		,'currency' => null
		/**
		 * 2017-10-20 «Stripe API Reference» → «Create a source» → «flow».
		 * «The authentication flow of the source to create.
		 * `flow` is one of `redirect`, `receiver`, `code_verification`, `none`.
		 * It is generally inferred unless a type supports multiple flows.»
		 * https://stripe.com/docs/api#create_source-flow
		 * https://stripe.com/docs/api#source_object-flow
		 * String, optional.
		 */
		,'flow' => null
		/**
		 * 2017-10-20 «Stripe API Reference» → «Create a source» → «metadata».
		 * «A set of key/value pairs that you can attach to a source object.
		 * It can be useful for storing additional information about the source in a structured format.»
		 * https://stripe.com/docs/api#create_source-metadata
		 * https://stripe.com/docs/api#source_object-metadata
		 * Hash, optional.
		 */
		,'metadata' => []
		/**
		 * 2017-10-20 «Stripe API Reference» → «Create a source» → «owner».
		 * «Information about the owner of the payment instrument
		 * that may be used or required by particular source types.»
		 * https://stripe.com/docs/api#create_source-owner
		 * https://stripe.com/docs/api#source_object-owner
		 * Hash, optional.
		 */
		,'owner' => [
			/**
			 * 2017-10-20 «Owner’s address».
			 * «Stripe API Reference» → «Create a source» → «owner» → «address».
			 * https://stripe.com/docs/api#create_source-owner-address
			 * https://stripe.com/docs/api#source_object-owner-address
			 * Hash, optional.
			 */
			'address' => $this->pAddress($a)
			/**
			 * 2017-10-20 «Owner’s email address».
			 * «Stripe API Reference» → «Create a source» → «owner» → «email».
			 * https://stripe.com/docs/api#create_source-owner-email
			 * https://stripe.com/docs/api#source_object-owner-email
			 * String, optional.
			 */
			,'email' => $a->getEmail()
			/**
			 * 2017-10-20 «Owner’s phone number (including extension)».
			 * «Stripe API Reference» → «Create a source» → «owner» → «phone».
			 * https://stripe.com/docs/api#create_source-owner-phone
			 * https://stripe.com/docs/api#source_object-owner-phone
			 * String, optional.
			 */
			,'phone' => df_phone_format_clean($a, false)
		]
		/**
		 * 2017-10-20 «Stripe API Reference» → «Create a source» → «redirect».
		 * «Parameters required for the redirect flow.
		 * Required if the source is authenticated by a redirect (`flow` is `redirect`).».
		 * https://stripe.com/docs/api#create_source-redirect
		 * https://stripe.com/docs/api#source_object-redirect
		 * Hash, optional.
		 */
		,'redirect' => [
			/**
			 * 2017-10-20 «Stripe API Reference» → «Create a source» → «redirect» → «return_url».
			 * «The URL you provide to redirect the customer back to you
			 * after they authenticated their payment».
			 * https://stripe.com/docs/api#create_source-redirect-return_url
			 * https://stripe.com/docs/api#source_object-redirect-return_url
			 * String, required.
			 */
			'return_url' => null
		]
		/**
		 * 2017-10-20 «Stripe API Reference» → «Create a source» → «statement_descriptor».
		 * «An arbitrary string to be displayed on your customer’s statement.
		 * As an example, if your website is "RunClub" and the item you’re charging for is a race ticket,
		 * you may want to specify a `statement_descriptor` of "RunClub 5K race ticket".
		 * While many payment types will display this information, some may not display it at all.».
		 * https://stripe.com/docs/api#create_source-statement_descriptor
		 * https://stripe.com/docs/api#source_object-statement_descriptor
		 * String, optional.
		 */
		,'statement_descriptor' => null
		/**
		 * 2017-10-20 «Stripe API Reference» → «Create a source» → «token».
		 * «An optional token used to create the source.
		 * When passed, token properties will override source parameters.».
		 * https://stripe.com/docs/api#create_source-token
		 * String, optional.
		 */
		,'token' => null
		/**
		 * 2017-10-20 «Stripe API Reference» → «Create a source» → «type».
		 * «The type of the source.
		 * The `type` is a payment method, one of:
		 * 		`alipay`, `bancontact`, `card`, `giropay`, `ideal`, `sepa_debit`, `sofort`, `three_d_secure`
		 * An additional hash is included on the source with a name matching this value.
		 * It contains additional information specific to the payment method used.»
		 * https://stripe.com/docs/api#source_object-type
		 */
		,'type' => 'card'
		/**
		 * 2017-10-20 «Stripe API Reference» → «Create a source» → «usage».
		 * «Either `reusable` or `single_use`.
		 * Whether this source should be reusable or not.
		 * Some source types may or may not be reusable by construction,
		 * while other may leave the option at creation.
		 * If an incompatible value is passed, an error will be returned.».
		 * https://stripe.com/docs/api#create_source-usage
		 * https://stripe.com/docs/api#source_object-usage
		 * String, optional.
		 *
		 * 2017-10-21 «Payment Methods Supported by the Sources API» → «Single-use or reusable».
		 * «Certain payment methods allow for the creation of sources
		 * that can be reused for additional payments
		 * without your customer needing to complete the payment process again.
		 * Sources that can be reused have their `usage` parameter set to `reusable`.
		 *
		 * Conversely, if a source can only be used once, this parameter is set to `single_use`
		 * and a source must be created each time a customer makes a payment.
		 * Such sources should not be attached to customers and should be charged directly instead.
		 * They can only be charged once and their status will transition to `consumed`
		 * when they get charged.
		 *
		 * Reusable sources must be attached to a `Customer` in order to be reused
		 * (they will get consumed as well if otherwise charged directly).
		 * Refer to the Sources & Customers guide to learn how to attach Sources to Customers
		 * and manage a Customer’s sources list.»
		 * https://stripe.com/docs/sources#single-use-or-reusable
		 * https://stripe.com/docs/sources/customers
		 */
		,'usage' => 'reusable'
	];}
}