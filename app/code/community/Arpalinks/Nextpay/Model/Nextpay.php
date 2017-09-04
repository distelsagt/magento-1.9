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

class Arpalinks_Nextpay_Model_nextpay extends Mage_Payment_Model_Method_Abstract
{
	protected $_code = 'nextpay';
	protected $_formBlockType = 'nextpay/form';
	protected $_infoBlockType = 'nextpay/info';
	protected $_isGateway               = false;
	protected $_canAuthorize            = true;
	protected $_canCapture              = true;
	protected $_canCapturePartial       = false;
	protected $_canRefund               = false;
	protected $_canVoid                 = false;
	protected $_canUseInternal          = false;
	protected $_canUseCheckout          = true;
	protected $_canUseForMultishipping  = false;
	protected $_order;

	public function getOrder()	{
		if (! $this->_order) {
			$paymentInfo = $this->getInfoInstance ();
			$this->_order = Mage::getModel ( 'sales/order' )->loadByIncrementId ( $paymentInfo->getOrder ()->getRealOrderId () );
		}
		return $this->_order;
	}

	public function validate() {
		$quote = Mage::getSingleton ( 'checkout/session' )->getQuote ();
		$quote->setCustomerNoteNotify ( false );
		parent::validate ();
	}

	public function getOrderPlaceRedirectUrl()	{
		return Mage::getUrl ( 'nextpay/redirect/redirect', array ('_secure' => true ) );
	}

	public function capture(Varien_Object $payment, $amount)	{
		$payment->setStatus ( self::STATUS_APPROVED )->setLastTransId ( $this->getTransactionId () );
		return $this;
	}

	public function getPaymentMethodType()	{
		return $this->_paymentMethod;
	}

	public function getUrl() {
		require_once Mage::getBaseDir().DS.'lib'.DS.'Zend'.DS.'Log.php';
		require_once Mage::getBaseDir().DS.'lib'.DS.'nusoap'.DS.'nusoap.php';

		$gateway = $this->getConfigData ('gateway');

		$client = new nusoap_client($gateway, 'wsdl');
    $client->soap_defencoding = 'UTF-8';

		$ApiKey 	= Mage::helper ('core')->decrypt($this->getConfigData ('terminal_Id')) ;
		$orderId 		= $this->getOrder ()->getRealOrderId ();
		Mage::getSingleton('core/session')->setOrderId(Mage::helper ('core')->encrypt($this->getOrder ()->getRealOrderId ()));
		$amount 		= intval($this->getOrder ()->getGrandTotal ());
		$callBackUrl 	= Mage::getBaseUrl() .'/nextpay/redirect/success/';

    $parameters = array(
			'api_key' 	=> $ApiKey,
			'amount' 		=> $amount,
			'order_id'=> $orderId,
			'callback_uri' 	=> $callBackUrl
    );


		$result = $client->call('TokenGenerator', array($parameters));
    $result = $result['TokenGeneratorResult'];
		if ($result['code'] == -1) {
				$pgwpay_url = $this->getConfigData ('pgwpay_url') ;
		} else {
				$msg 	= Mage::Helper('nextpay')->getBankMessage($result['code']);
				$this->getOrder ();
				$this->_order->addStatusToHistory ( Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $this->_getHelper()->__($msg), true );
				$this->_order->save ();
				Mage::getSingleton('checkout/session')->setErrorMessage($this->_getHelper()->__($msg)) ;
  	}

		return $result ;
}
	public function getFormFields() {
		$orderId = $this->getOrder ()->getRealOrderId ();
		//$customerId = Mage::getSingleton ( 'customer/session' )->getCustomerId ();
		$params = array('x_invoice_num' => $orderId) ;
		return $params;
	}
}
