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

class Arpalinks_Nextpay_Helper_Data extends Mage_Payment_Helper_Data
{

	public function getBankMessage($messageNumber) {

		switch($messageNumber){
			case 0:
				$msg = "تراكنش با موفقیت انجام شد.";
				break ;
			default:
				$msg = "خطایی در پرداخت رخ داده است : {$messageNumber} " ;
		}

		return $msg ;
	}
}
