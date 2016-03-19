<?php
namespace Dfe\Stripe\Element;
use Magento\Framework\Data\Form\Element\AbstractElement as AE;
class Webhook extends AE {
	/**
	 * 2016-03-18
	 * @override
	 * @see \Magento\Framework\Data\Form\Element\AbstractElement::getElementHtml()
	 * @return string
	 */
	public function getElementHtml() {return df_url_frontend('dfe-stripe');}
}