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

class Arpalinks_Nextpay_Block_Success extends Mage_Core_Block_Template
{
	protected function _toHtml() {
    	$TransactionID 			= $_POST['trans_id'];

			$oderId =  Mage::helper('core')->decrypt(Mage::getSingleton('core/session')->getOrderId());

			if (!$orderId && isset($_POST['order_id']))
					$oderId = $_POST['order_id'];

			Mage::getSingleton('core/session')->unsOrderId();


			// $order = new Mage_Sales_Model_Order();
      // $incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
      // $order->loadByIncrementId($incrementId);
			$order = Mage::getModel('sales/order')->loadByIncrementId($oderId);
 			$this->_paymentInst = $order->getPayment()->getMethodInstance();

			require_once Mage::getBaseDir().DS.'lib'.DS.'Zend'.DS.'Log.php';
			require_once Mage::getBaseDir().DS.'lib'.DS.'nusoap'.DS.'nusoap.php';

			$gateway = $this->_paymentInst->getConfigData ('gateway');
			$client = new nusoap_client($gateway, 'wsdl');
			$client->soap_defencoding = 'UTF-8';
			if ( (!$client) OR ($err = $client->getError()) ) {
				$this->_order->addStatusToHistory ( Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper ( 'nextpay' )->__('Could not connect to bank or service.'), true );
				$this->_order->save ();
				Mage::getSingleton('checkout/session')->setErrorMessage($this->__('Could not connect to bank or service.')) ;
				$html = '<html><body> <script type="text/javascript"> window.location = "' . Mage::getUrl ( 'checkout/onepage/failure', array ('_secure' => true) ) . '" </script></body></html>';
				return $html;

			}else{
				$ApiKey 	= Mage::helper ( 'core' )->decrypt($this->_paymentInst->getConfigData('terminal_Id')) ;
				$amount = intval($order->getGrandTotal());
			 	$parameters = array(
					'api_key' 		=> $ApiKey,
					'trans_id' 			=> $TransactionID,
					'order_id' => $oderId,
  			 	'amount'		=> $amount
		    );
		    $i = 3; //to garantee the connection and authorization, this process should be repeat maximum 10 times

				do {

					$verify_result = $client->call('PaymentVerification', $parameters);
					if ($client->fault || ($err = $client->getError())) {
						$i -= 1;
					}else{
						$i = 0 ;
					}
     	 	} while($i>0);

		  		if ($client->fault){
	            	ob_start();
								echo "<h2>خطا</h2><pre>" ;
								print_r($verify_result);
								echo "<pre>" ;
	            	$content = ob_get_contents();
	            	ob_end_clean();

	            	$order->addStatusToHistory ( Mage_Sales_Model_Order::STATE_CANCELED, $content, true );
								$order->save ();
								Mage::getSingleton('checkout/session')->setErrorMessage($content) ;
								$html = '<html><body> <script type="text/javascript"> window.location = "' . Mage::getUrl ( 'checkout/onepage/failure', array ('_secure' => true) ) . '" </script></body></html>';
								return $html;
          }else{

							$err = $client->getError();
							if ($err) {
								// Display the error
								$order->addStatusToHistory ( Mage_Sales_Model_Order::STATE_CANCELED, $err, true );
								$order->save ();
								Mage::getSingleton('checkout/session')->setErrorMessage($err) ;
								$html = '<html><body> <script type="text/javascript"> window.location = "' . Mage::getUrl ( 'checkout/onepage/failure', array ('_secure' => true) ) . '" </script></body></html>';
								return $html;

							}
							else {
									$verify_result = $verify_result['PaymentVerificationResult'];
									if($verify_result['code'] == 0){
											if ($order->canInvoice ()) {
														$invoice = $order->prepareInvoice ();
														$invoice->register ()->capture ();
														Mage::getModel ( 'core/resource_transaction' )
															->addObject ( $invoice )
															->addObject ( $invoice->getOrder() )
															->save ();
														$message = sprintf($this->__("Yours order track number is %s"),$TransactionID);
														$order->addStatusToHistory ( $this->_paymentInst->getConfigData ( 'second_order_status' ), Mage::helper ( 'nextpay' )->__( Mage::Helper('nextpay')->getBankMessage($verify_result['code'])) . " " . $message, true );
														$order->save ();
														$order->sendNewOrderEmail ();
														Mage::getSingleton('core/session')->addSuccess($message);
														$html = '<html><body> <script type="text/javascript"> window.location = "' . Mage::getUrl ( 'checkout/onepage/success', array ('_secure' => true ) ) . '" </script> </body></html>';
														return $html;
											}
								}else {
											$msg = Mage::Helper('nextpay')->getBankMessage($verify_result['code']);
											$this->_order = Mage::getModel ( 'sales/order' )->loadByIncrementId ( $oderId );
											$this->_order->addStatusToHistory ( Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper ( 'nextpay' )->__( $msg), true );
											$this->_order->save ();
											$this->_order->sendOrderUpdateEmail (true, $msg);
											Mage::getSingleton('checkout/session')->setErrorMessage($this->__($msg)) ;
											$html = '<html><body> <script type="text/javascript"> window.location = "' . Mage::getUrl ( 'checkout/onepage/failure', array ('_secure' => true) ) . '" </script> </body></html>';
											return $html;
									}
	            }

          }
			}

	}
}
