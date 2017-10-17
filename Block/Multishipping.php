<?php
namespace Dfe\Stripe\Block;
use Dfe\Stripe\ConfigProvider as CP;
use Magento\Framework\View\Element\AbstractBlock as _P;
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
	final protected function _toHtml() {$m = $this->m(); return df_cc_n(
		df_tag('div',
			// 2017-08-26
			// The generic «.df-payment» selector is used here:
			// https://github.com/mage2pro/core/blob/2.10.43/Payment/view/frontend/web/main.less#L51
			['class' => df_cc_s('df-payment df-card', df_module_name_lc($m, '-'))]
			+ df_widget($m, 'multishipping', CP::p())
			,df_block_output($m, 'multishipping')
		)
		,df_link_inline(df_asset_name('main', $m, 'css'))
	);}
}