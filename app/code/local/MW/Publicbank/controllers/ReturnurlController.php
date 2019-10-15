<?php

class MW_Publicbank_ReturnurlController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        //echo 'return';

        /*
		$order = Mage::getModel('sales/order')->loadByIncrementId("100000068");
			$order->setStatus('holded');
			$order->save();
		die;*/
        //$strResult = $this->getRequest()->getPost();//'000818370000000000010000044761320915000000000900';

        $data = $this->getRequest()->getPost();
        Mage::log('Result Payment ' . date('d-m-Y H:i:s'), null, 'easy.log');
        Mage::log(print_r($data, true), null, 'easy.log');
        //die;

        $dataString = '';
        foreach ($data as $key => $value) {
            $dataString .= '/' . $key . '/' . $value;
        }
        //Mage::log('$dataString 1 : '.trim($dataString,'/'),null,'request.log');
        //$dataString = base64_encode($dataString);
        Mage::log('$dataString : ' . $dataString, null, 'easy.log');
        if (isset($data['TM_Status']) && isset($data['TM_RefNo'])) {
            $this->update($data);
        }
        return $this;


//        $arr_res = $this->resultSplit($strResult);
//        Mage::log('Publicbank payment');
//        Mage::log('Result string : '.$strResult);
//        foreach($arr_res as $k=>$val){
//            Mage::log($k.' : '.$val);
//        }
//        $now = Mage::getModel('core/date')->timestamp(time());
//        Mage::log(date('H:i:s d-m-Y',$now));
//        if(isset($arr_res['invoiceNumb']) && $arr_res['invoiceNumb'] != '' && isset($arr_res['response']) && $arr_res['response'] != ''){
//            $this->update($arr_res);
//        }
//        return $this;
        /*
    	if ($tmstatus == "YES")
		{
			Mage::log('Update to complete');
			$order = Mage::getModel('sales/order')->loadByIncrementId($ref);		
			$order->setStatus('payment_success');
			$order->sendNewOrderEmail();			
			$order->save();
		}
		else 
		{
			Mage::log('Update OnHold:');
			$order = Mage::getModel('sales/order')->loadByIncrementId($ref);		
			$order->setStatus('holded');
			$order->sendNewOrderEmail();
			$order->setEmailSent(true);
			$order->save();
		}
        */
    }


    public function update($arr_data)
    {
        $data = $arr_data;
        $status = $data['TM_Status'];
        $orderId = intval($data['TM_RefNo']);
        Mage::log('$orderId-update : ' . $orderId, null, 'easy.log');
        if (isset($orderId) && trim($orderId) != '') {
//            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
//            $payment = $order->getPayment();
//            $payment->save();
            if ($status == 'YES') {
                $order = Mage::getModel('sales/order');
                $order->loadByIncrementId($orderId);
                if ($order->getCanSendNewEmailFlag()) {
                    try {
                        //$order->sendNewOrderEmail();
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
                //    Webkul_Marketplace_Model_Saleslist::getProductSalesCalculation($order);
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'DBS has authorized the payment.');
                $order->save();


            } else {
                $order = Mage::getModel('sales/order');
                $order->loadByIncrementId($orderId);
                $order->cancel();
                //$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'DBS Gateway has declined the payment. Reason: '.$errormsg);
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'DBS Gateway has declined the payment.');
                $order->save();
            }
//            if(isset($status) && $status=='YES'){
//                try {
//                    $order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
//                    try {
//                        if(!$order->canInvoice()){
//                            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
//                        }
//                        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
//                        if (!$invoice->getTotalQty()) {
//                            Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
//                        }
//                        $invoice->register();
//                        $transactionSave = Mage::getModel('core/resource_transaction')
//                            ->addObject($invoice)
//                            ->addObject($invoice->getOrder());
//                        $transactionSave->save();
//                    }
//                    catch (Mage_Core_Exception $e) {
//                        Mage::log($e->getMessage());
//                    }
//                    $order->sendNewOrderEmail();
//                    $order->setEmailSent(true);
//                    $order->setData('status', 'processing');
//                    $order->save();
//
//                    /**
//                     * Ocean: subtract inventory
//                     */
//                    //$order->subtractInventory();
//
//                } catch (Exception $e) {
//                    Mage::log($e->getMessage());
//                }
//            }else{
//                $order->cancel();
//                $order->setData('status', 'canceled');
//                $order->save();
//            }
        }
        //$this->returncomplete();
        return $this;
    }

    public function returnAction()
    {
        $_SESSION['order_processing_status'] = 0;
        $session = Mage::getSingleton('checkout/session');
        Mage::log('$session->getLastRealOrderId() : ' . $session->getLastRealOrderId(), null, 'easy.log');
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            Mage::log('$order->getId() : ' . $order->getId(), null, 'easy.log');
            if ($order->getId()) {
                if ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED || $order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                    foreach ($order->getAllStatusHistory() as $orderComment) {
                        if ($orderComment->getData('comment') != null && $orderComment->getData('comment') != '') {
                            Mage::getSingleton('checkout/session')->addError($orderComment->getData('comment'));
                        }
                    }

                    if ($quoteId = $session->getLastQuoteId()) {
                        Mage::log('$quoteId : ' . $quoteId, null, 'easy.log');
                        $quote = Mage::getModel('sales/quote')->load($quoteId);
                        if ($quote->getId()) {
                            Mage::log('$quote->getId() : ' . $quote->getId(), null, 'easy.log');
                            $quote->setIsActive(true)->save();
                            $session->setQuoteId($quoteId);
                        }
                    }
                    $this->_redirect('checkout/cart');
                    $_SESSION['order_processing_status'] = 0;
                }
                if ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING) {
                    Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
                    $this->_redirect('checkout/onepage/success', array('_secure' => true));
                    $_SESSION['order_processing_status'] = 1;



                    // Tung edit

                    $percent = Mage::getStoreConfig('marketplace/marketplace_options/percent');
                    $lastOrderId = $order->getId();

                    Mage::dispatchEvent('mp_discount_manager', array("order" => $order));
                    $discountDetails = Mage::getSingleton('core/session')->getData('salelistdata');
                    $count = count($order->getAllItems());
                    $array = array();
                    $seller = array();
                    $simple = array();
                    $conf = array();
                    $quan = array();
                    foreach ($order->getAllItems() as $item) {
                        $quan[$item->getProductId()] = $item->getQtyOrdered() * 1;
                        $array[] = $item->getProductId();
                        $product = Mage::getModel('catalog/product')->load($item->getProductId());
                        $productType = $product->getTypeID();
                        if ($productType == 'simple') {
                            $simple[] = $item->getProductId();
                        } else {
                            $conf[] = $item->getProductId();
                        }
                        $productowner = Mage::getModel('marketplace/product')->isCustomerProduct($item->getProductId());
                        $id = $productowner['userid'];
                        if ($id != '') {
                            $k = 0;
                            foreach ($seller as $part) {
                                if ($part == $id) {
                                    $k = 1;
                                }
                            }
                            if ($k == 0) {
                                $seller[] = $id;
                            }
                        }


                        //$item_data = $item->getData();
                        //$attrselection = unserialize($item_data['product_options']);
                        //$bundle_selection_attributes = unserialize($attrselection['bundle_selection_attributes']);
                        /*if (!$bundle_selection_attributes['option_id']) {
                            $temp = $item->getProductOptions();
                            if (array_key_exists('seller_id', $temp['info_buyRequest'])) {
                                $seller_id = $temp['info_buyRequest']['seller_id'];
                            } else {
                                $seller_id = '';
                            }
                            $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
                            $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
                            $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
                            $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
                            if (!$rates[$currentCurrencyCode]) {
                                $rates[$currentCurrencyCode] = 1;
                            }
                            if ($discountDetails[$item->getProductId()])
                                $price = $discountDetails[$item->getProductId()]['price'] / $rates[$currentCurrencyCode];
                            else
                                $price = $item->getPrice() / $rates[$currentCurrencyCode];
                            if ($seller_id == '') {
                                $collection_product = Mage::getModel('marketplace/product')->getCollection();
                                $collection_product->addFieldToFilter('mageproductid', array('eq' => $item->getProductId()));
                                foreach ($collection_product as $selid) {
                                    $seller_id = $selid->getuserid();
                                }
                            }
                            if ($seller_id == '') {
                                $seller_id = 0;
                            }
                            //

                            //
                            $collection1 = Mage::getModel('marketplace/saleperpartner')->getCollection();
                            $collection1->addFieldToFilter('mageuserid', array('eq' => $seller_id));
                            $taxamount = $item_data['tax_amount'];
                            $qty = $item->getQtyOrdered();
                            $totalamount = $qty * $price;

                            if (count($collection1) != 0) {
                                foreach ($collection1 as $rowdatasale) {
                                    $commision = ($totalamount * $rowdatasale->getcommision()) / 100;
                                }
                            } else {
                                $commision = ($totalamount * $percent) / 100;
                            }
                            $wholedata['id'] = $item->getProductId();
                            Mage::dispatchEvent('mp_advance_commission', $wholedata);
                            $advancecommission = Mage::getSingleton('core/session')->getData('commission');
                            if ($advancecommission != '') {
                                $percent = $advancecommission;
                                $commType = Mage::getStoreConfig('mpadvancecommission/mpadvancecommission_options/commissiontype');
                                if ($commType == 'fixed') {
                                    $commision = $percent;
                                } else {
                                    $commision = ($totalamount * $advancecommission) / 100;
                                }
                                if ($commision > $totalamount) {
                                    $commission = $totalamount * (Mage::getStoreConfig('marketplace/marketplace_options/percent')) / 100;
                                }
                            }

                            $actparterprocost = $totalamount - $commision;
                            $collectionsave = Mage::getModel('marketplace/saleslist');
                            $collectionsave->setmageproid($item->getProductId());
                            $collectionsave->setmageorderid($lastOrderId);
                            $collectionsave->setmagerealorderid($order->getIncrementId());
                            $collectionsave->setmagequantity($qty);
                            $collectionsave->setmageproownerid($seller_id);
                            $collectionsave->setcpprostatus(0);
                            $collectionsave->setmagebuyerid(Mage::getSingleton('customer/session')->getCustomer()->getId());
                            $collectionsave->setmageproprice($price);
                            $collectionsave->setmageproname($item->getName());
                            if ($totalamount != 0) {
                                $collectionsave->settotalamount($totalamount);
                            } else {
                                $collectionsave->settotalamount($price);
                            }
                            $collectionsave->setTotaltax($taxamount);
                            if (Mage::getStoreConfig('marketplace/marketplace_options/taxmanage')) {
                                $actparterprocost = $actparterprocost + $taxamount;
                            }
                            $collectionsave->settotalcommision($commision);
                            $collectionsave->setactualparterprocost($actparterprocost);
                            $collectionsave->setcleared_at(date('Y-m-d H:i:s'));
                            $collectionsave->save();
                            $qty = '';
                        }*/
                    }

                    foreach ($seller as $test) {
                        //if($seller_id!=''){
                        $sell = Mage::getModel('customer/customer')->load($test);
                        $emailTemplate = Mage::getModel('core/email_template')
                            ->loadDefault('notify_new_customer1');
                        $emailreceive = Mage::getStoreConfig('trans_email/ident_general/email');
                        $namereceive = Mage::getStoreConfig('trans_email/ident_general/name');
                        // $seller = Mage::getModel('customer/customer')->load($seller_id);

                        $shipping_address = $order->getShippingAddress()->format('html');
                        $flag = "";
                        $orderinfo = '';
                        $style = 'style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc";';
                        //

                        // die();
                        //
                        foreach ($conf as $con) {
                            $productowner = Mage::getModel('marketplace/product')->isCustomerProduct($con);
                            if ($productowner['userid'] == $test) {
                                $product = Mage::getModel('catalog/product')->load($con);
                                $configurable = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
                                $simpleCollection = $configurable->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
                                foreach ($simpleCollection as $simpleProduct) {
                                    $an = $simpleProduct->getId();
                                    foreach ($simple as $sim) {
                                        if ($sim == $an) {
                                            //$flag.=','.$sim;
                                            $simproduct = Mage::getModel('catalog/product')->load($sim);
                                            $flag .= ',' . $simproduct->getName();
                                            $flag .= ',' . $simproduct->getSku();
                                            $flag .= ',' . $quan[$sim];
                                            $orderinfo = $orderinfo . "<tr>
								<td valign='top' align='left' " . $style . " >" . $simproduct->getName() . "</td>
								<td valign='top' align='left' " . $style . ">" . $simproduct->getSku() . "</td>
								<td valign='top' align='left' " . $style . " >" . $quan[$sim] . "</td>
							 </tr>";
                                        }
                                    }

                                }
                                // $flag.=','.$con;
                            }
                        }
                        foreach ($simple as $sim1) {
                            $productowner1 = Mage::getModel('marketplace/product')->isCustomerProduct($sim1);
                            if ($productowner1['userid'] == $test) {
                                //$flag.=','.$sim;
                                $simproduct = Mage::getModel('catalog/product')->load($sim1);
                                $flag .= ',' . $simproduct->getName();
                                $flag .= ',' . $simproduct->getSku();
                                $flag .= ',' . $quan[$sim1];
                                $orderinfo = $orderinfo . "<tr>
								<td valign='top' align='left' " . $style . " >" . $simproduct->getName() . "</td>
								<td valign='top' align='left' " . $style . ">" . $simproduct->getSku() . "</td>
								<td valign='top' align='left' " . $style . " >" . $quan[$sim1] . "</td>
							 </tr>";
                                // $flag.=','.$con;
                            }

                        }

                        $emailTemplateVariables["myvar5"] = $order->getRealOrderId();
                        //Mage::log($order->getRealOrderId(), null, 'order_id.log', true);
                        $emailTemplateVariables["myvar7"] = $sell->getEmail();
                        $emailTemplateVariables["myvar1"] = $sell->getName();
                        $emailTemplateVariables["myvar4"] = $shipping_address;
                        $emailTemplateVariables["myvar6"] = $test;
                        $emailTemplateVariables["myvar8"] = $orderinfo;
                        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
                        $subject = "New Shipment Request for Order # " . $order->getRealOrderId() . " [Bugis Street Online Store]";
                        $adminEmail = Mage::getStoreConfig('trans_email/ident_general/email');
                        $adminName = Mage::getStoreConfig('trans_email/ident_general/name');
                        $mail = Mage::getModel("core/email")
                            ->setFromName($adminName)
                            ->setFromEmail($adminEmail)
                            ->setToName($sell->getName())
                            ->setToEmail($sell->getEmail())
                            ->setBody($processedTemplate)
                            ->setSubject($subject)
                            ->setType("html");

                        //Mage::log($order->getStatus(), null, 'order_status.log', true);
                        try {
                                $mail->send();

                        } catch (Exception $error) {
                            Mage::getSingleton("core/session")->addError($error->getMessage());
                            return false;
                        }

                    }

                    //End Tung
                    //Webkul_Marketplace_Model_Saleslist::getProductSalesCalculation($order);


                } else {
                    $_SESSION['order_processing_status'] = 0;
                }
            } else {
                Mage::getSingleton('checkout/session')->addError('No order for processing found');
                $this->_redirect('checkout/cart');
            }
        } else {
            Mage::getSingleton('checkout/session')->addError('No order for processing found');
            $this->_redirect('checkout/cart');
            $_SESSION['order_processing_status'] = 0;
        }
    }


    public function resultSplit($str)
    {
        $data = array();
        $data['response'] = substr($str, 0, 2);
        $data['authCode'] = substr($str, 2, 6);
        $data['invoiceNumb'] = substr($str, 8, 20);
        $data['PAN'] = substr($str, 28, 4);
        $data['expiryDate'] = substr($str, 32, 4);
        $data['Amount'] = substr($str, 36, 12);
        return $data;

    }
}