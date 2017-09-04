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

class Arpalinks_Nextpay_Block_Redirect extends Mage_Core_Block_Abstract
{

	protected function _toHtml() {
		$module = 'arpalinks/nextpay';
		$payment = $this->getOrder ()->getPayment ()->getMethodInstance ();
		$res = $payment->getUrl () ;
		if ($res['code'] == "-1") {
       error_log('Transaction ID' . $res['trans_id']);
			 $html = '<html><body> <script type="text/javascript"> window.location = "https://api.nextpay.org/gateway/payment/' . $res['trans_id'] . '" </script> </body></html>';
		}else{
			$html = '<html><body> <script type="text/javascript"> window.location = "' . Mage::getUrl ( 'checkout/onepage/failure', array ('_secure' => true) ) . '" </script> </body></html>';
		}
		return $html;
	}
}
