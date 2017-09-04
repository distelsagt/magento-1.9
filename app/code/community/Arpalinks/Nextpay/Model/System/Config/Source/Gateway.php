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

class Arpalinks_Nextpay_Model_System_Config_Source_Gateway
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	return array(
    		array('value' => '0', 'label' => ' -- سرور خود را انتخاب کنید -- '),
    		array('value' => 'https://api.nextpay.org/gateway/token.wsdl', 'label' => 'نکست پی')
    	);
    }
}
