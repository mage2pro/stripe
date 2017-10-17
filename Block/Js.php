<?php
namespace Dfe\Stripe\Block;
use Magento\Framework\View\Element\AbstractBlock as _P;
/**
 * 2017-10-16
 * Note 1.
 * «Including Stripe.js»: https://stripe.com/docs/stripe.js#including-stripejs
 * «To best leverage Stripe’s advanced fraud functionality,
 * include this script on every page on your site, not just the checkout page.
 * This allows Stripe to detect anomalous behavior
 * that may be indicative of fraud as users browse your website.»
 * https://github.com/mage2pro/stripe/issues/33
 * Note 2.
 * I have implemented it by analogy with @see \Df\Intl\Js:
 * https://github.com/mage2pro/core/blob/3.2.3/Intl/Js.php#L1-L29
 * @final Unable to use the PHP «final» keyword here because of the M2 code generation.
 * Note 3.
 * `Do I need to call Stripe(publishableKey[, options]) constructor
 * on every page «to best leverage Stripe’s advanced fraud functionality»?`: https://mage2.pro/t/4704
 */
class Js extends _P {
	/**
	 * 2017-10-16
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
	 * @return string
	 */
	final protected function _toHtml() {return !dfps($this)->enable() ? '' : df_js(
		null, 'https://js.stripe.com/v3/'
	);}
}