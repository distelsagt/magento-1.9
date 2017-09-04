<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * @category   Payment
 * @package    Arpalinks_Nextpay
 * @copyright  Copyright (c) 2017 Nextpay (http://www.nextpay.ir)
 */

class Arpalinks_Nextpay_Block_Form extends Mage_Payment_Block_Form
{
	protected function _construct() {
		parent::_construct ();
		$this->setTemplate ( 'arpalinks/nextpay/form.phtml' );
	}

	public function getPaymentImageSrc() {
		return $this->getSkinUrl ( 'images/arpalinks/nextpay.png' );
	}

}
