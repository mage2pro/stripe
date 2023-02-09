<?php
namespace Dfe\Stripe\FE;
use Dfe\Stripe\Settings as S;
/**
 * 2017-10-15
 * «Brazilian Stripe accounts (currently in Preview) can only charge in Brazilian Real»:
 * https://github.com/mage2pro/stripe/issues/31
 * «Mexican Stripe accounts (currently in Preview) can only charge in Mexican Peso»
 * https://github.com/mage2pro/stripe/issues/32
 * @final Unable to use the PHP «final» keyword here because of the M2 code generation.
 */
class Currency extends \Df\Directory\FE\Currency {
	/**
	 * 2017-10-15
	 * @final Unable to use the PHP «final» keyword here because of the M2 code generation.
	 * @override
	 * Перекрываем магический метод,
	 * потому что к магическим методам не применяются плагины, а нам надо применить плагин
	 * @see \Df\Framework\Plugin\Data\Form\Element\AbstractElement::afterGetComment()
	 * @used-by \Magento\Config\Block\System\Config\Form\Field::_renderValue()
	 * https://github.com/magento/magento2/blob/2.2.0-RC1.8/app/code/Magento/Config/Block/System/Config/Form/Field.php#L79-L81
	 *	if ((string)$element->getComment()) {
	 *		$html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
	 *	}
	 */
	function getComment():string {return !$this->hasKey() || $this->s()->isMerchantInUS() ?
		"<ul class='df-note'><li>The <b>JCB</b>, <b>Discover</b>, and <b>Diners Club</b> bank cards are <b>always charged in USD</b> (<a href='https://github.com/mage2pro/stripe/issues/28' target='_blank' title='«JCB, Discover, and Diners Club cards can only be charged in USD»'>Stripe does not support other currencies for them</a>).</li>
		</ul>"
		: (!$this->disabled() ? '' :
			__("The <b>%1</b> Stripe accounts can only charge in <b>%2</b>.",
				dftr($this->country(), ['BR' => __('Brazilian'), 'MX' => __('Mexican')])
				,df_currency_name($this->currency()
			))
		)
	;}

	/**
	 * 2017-10-15
	 * @override
	 * @see \Df\Directory\FE\Currency::getValue()
	 * @used-by \Df\Framework\Form\Element\Select2::setRenderer()
	 * @return string|null
	 */
	function getValue() {return $this->disabled() ? $this->currency() : parent::getValue();}

	/**
	 * 2017-10-15
	 * @override
	 * @see \Df\Framework\Form\Element\Select2::enabled()
	 * @used-by self::getValue()
	 * @used-by \Df\Framework\Form\Element\Select2::setRenderer()
	 */
	final protected function disabled():bool {return in_array($this->country(), ['BR', 'MX']);}

	/**
	 * 2017-10-15
	 * @used-by self::currency()
	 * @used-by self::disabled()
	 * @used-by self::getComment()
	 */
	private function country():string {return dfc($this, function() {return !$this->hasKey() ? '' :
		$this->s()->merchantCountry()
	;});}

	/**
	 * 2017-10-15
	 * @used-by self::getComment()
	 * @used-by self::getValue()
	 */
	private function currency():string {return df_currency_by_country_c($this->country());}

	/**
	 * 2017-10-15
	 * @used-by self::country()
	 * @used-by self::getComment()
	 */
	private function hasKey():bool {return dfc($this, function():bool {return !!$this->s()->privateKey(null, false);});}

	/**
	 * 2017-10-15
	 * @used-by self::country()
	 * @used-by self::getComment()
	 * @used-by self::hasKey()
	 * @return S $s
	 */
	private function s() {return dfc($this, function() {return dfps($this);});}
}