<?php

namespace Glocash\Checkout\Controller\Standard;

use Glocash\Checkout\Helper\Logs;

class Responsenotify extends \Glocash\Checkout\Controller\Pay
{

    public function execute()
    {
		 
        // Initialize return url

        try {
            $paymentMethod = $this->getPaymentMethod();

            // Get params from response
            $params = $this->getRequest()->getParams();

            // Create the order if the response passes validation
            if ($paymentMethod->validateResponse($params))
            {

                try {
					Logs::logw("notify Result:".json_encode($params),"glocash.log","Sign_verification");
					$a=20;
					while($a>0){
						
						$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
						//$order = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($params['REQ_INVOICE']);
						$order = $objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId($params['REQ_INVOICE']);

						if(!empty($order->getId())){
							
							Logs::logw("have order , order_id:".$order->getId()." order_number:".$params['REQ_INVOICE'],"glocash.log","Order_modification");
							if ($params['PGW_CURRENCY'] == $params['BIL_CURRENCY'] && strval($order->getGrandTotal()) != $params['PGW_PRICE']) {
								$comment = "order:".$order->getOrderCurrencyCode()." grandTotal=".strval($order->getGrandTotal())." , no equal to PGW_PRICE=:".$params['PGW_PRICE'];
								Logs::logw($comment,"glocash.log","");
							}
							else{
								$payment = $order->getPayment();
								$paymentMethod->postProcessing($order, $payment, $params);
							}
							break;
						}
						$a--;
						sleep(1);
					}
					
					if($a==0){
						Logs::logw("No order was found , order_number:".$params['REQ_INVOICE'],"glocash.log","Order_modification");
					}
					


                    /*if ($order) {
                        $this->getCheckoutSession()->setLastOrderId($order->getId())
                            ->setLastRealOrderId($order->getIncrementId())
                            ->setLastOrderStatus($order->getStatus());
                    }*/

                } catch (\Exception $e) {
					Logs::logw($e->getMessage(),"glocash.log","Error");
                     throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                }

            }
            else
            {
				Logs::logw("Validation failed Result:".json_encode($params),"glocash.log","Sign_verification");
                 throw new \Magento\Framework\Exception\LocalizedException(__('Validation failed'));
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
			Logs::logw($e->getMessage(),"glocash.log","Error");
             throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        } catch (\Exception $e) {
			Logs::logw($e->getMessage(),"glocash.log","Error");
             throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }


    }

}
