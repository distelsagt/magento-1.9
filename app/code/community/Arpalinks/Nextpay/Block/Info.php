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

class Arpalinks_Nextpay_Block_Info extends Mage_Payment_Block_Info
{
	protected function _construct() {
		parent::_construct ();
		$this->setTemplate ( 'arpalinks/nextpay/info.phtml' );
	}
	public function getMethodCode() {
		return $this->getInfo ()->getMethodInstance ()->getCode ();
	}
	public function toPdf() {
		$this->setTemplate ( 'arpalinks/nextpay/pdf/info.phtml' );
		return $this->toHtml ();
	}

}
