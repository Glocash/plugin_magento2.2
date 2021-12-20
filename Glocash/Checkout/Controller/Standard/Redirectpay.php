<?php

namespace Glocash\Checkout\Controller\Standard;

use Glocash\Checkout\Helper\Logs;

class Redirectpay extends \Glocash\Checkout\Controller\Pay
{

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_cancelPayment();
            $this->_checkoutSession->restoreQuote();
            $this->getResponse()->setRedirect(
                $this->getCheckoutHelper()->getUrl('checkout')
            );
        }
        
        $quote = $this->getQuote();
        $email = $this->getRequest()->getParam('email');
        if ($this->getCustomerSession()->isLoggedIn()) {
            $this->getCheckoutSession()->loadCustomerQuote();
            $quote->updateCustomerData($this->getQuote()->getCustomer());
        }
        else
        {
            $quote->setCustomerEmail($email);
        }
        $quote->reserveOrderId();
        $this->quoteRepository->save($quote);
				

        $params = [];
        $params["fields"] = $this->getPaymentMethod()->buildCheckoutRequest($quote);
		
		$json=$this->getPaymentMethod()->getGlocashUrl($quote);
		
		$arr=json_decode($json,true);
        $params["url"] = $arr["url"];
		if(empty($params["url"])){
			$params["url"] =$this->getCheckoutHelper()->getUrl('checkout');
		}

        return  $this->resultJsonFactory->create()->setData($params);
    }
	
	
	

}
