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
	 * @return string|null
	 */
	final protected function _toHtml() {
		$m = $this->m(); /** @var M $m */
		$s = $m->s(); /** @var S $s */
		$a = df_quote()->getBillingAddress(); /** @var A $a */
		return df_cc_n(df_tag('div',
			// 2017-08-26
			// The generic «.df-payment» selector is used here:
			// https://github.com/mage2pro/core/blob/2.10.43/Payment/view/frontend/web/main.less#L51
			['class' => df_cc_s('df-payment df-card', df_module_name_lc($m, '-'), 'df-singleLineMode')]
			+ df_widget($m, 'multishipping', CP::p() + ['ba' => $a->getData()])
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
	 * @param A $a
	 * @return string
	 */
	private function cardholder(A $a) {return transliterator_transliterate('Any-Latin; Latin-ASCII',
		df_strtoupper(df_cc_s($a->getFirstname(), $a->getLastname()))
	);}
}