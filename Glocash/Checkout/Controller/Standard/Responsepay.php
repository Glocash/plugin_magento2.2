<?php

namespace Glocash\Checkout\Controller\Standard;

use Glocash\Checkout\Helper\Logs;

class Responsepay extends \Glocash\Checkout\Controller\Pay
{

    public function execute()
    {
		 
        // Initialize return url
        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout');
		$orderNumber="";

        try {
            $paymentMethod = $this->getPaymentMethod();
            
            // Get payment method code
            $code = $paymentMethod->getCode();

            // Get params from response
            $params = $this->getRequest()->getParams();

            // Get quote from session
            $quoteId = $this->getQuote()->getId();
            $quote = $this->_quote->load($quoteId);
			
            // Setup params for hash check
            $orderNumber = $params['REQ_INVOICE'];

            $orderTotal = number_format($quote->getGrandTotal(), 2, '.', '');
            $orderKey = $this->getRequest()->getParam('key');

            // Create the order if the response passes validation
				
				$returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');

                if ($this->getCustomerSession()->isLoggedIn()) {
                    $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
                }
                else {
                    $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
                }

                $quote->setCustomerEmail($params['CUS_EMAIL']);
                $quote->setPaymentMethod($code);
                $quote->getPayment()->importData(['method' => $code]);
                $quote->save();

                $this->initCheckout();
                try {
                    $this->cartManagement->placeOrder($this->_checkoutSession->getQuote()->getId(), $this->_quote->getPayment());
                    $order = $this->getOrder();
                    $payment = $order->getPayment();
					
					Logs::logw("order_id:".$order->getId()." order_number:".$orderNumber,"glocash.log","Create_order");

                    /*if ($order) {
                        $this->getCheckoutSession()->setLastOrderId($order->getId())
                            ->setLastRealOrderId($order->getIncrementId())
                            ->setLastOrderStatus($order->getStatus());
                    }*/

                } catch (\Exception $e) {
					Logs::logw("order_number:".$orderNumber."  We can\'t place the order","glocash.log","Error");
                    $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
					$returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
                }


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
			Logs::logw("order_number:".$orderNumber."  ".$e->getMessage(),"glocash.log","Error");
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
			$returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
        } catch (\Exception $e) {
			Logs::logw("order_number:".$orderNumber."  ".$e->getMessage(),"glocash.log","Error");
            $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
			$returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
        }

        $this->getResponse()->setRedirect($returnUrl);

    }

}
