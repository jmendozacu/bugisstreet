<?php
Class Webkul_Marketplace_Model_Observer
{
	public function deleteCustomer($observer){
		$sellerid=$observer->getCustomer()->getId();
		$sellers=Mage::getModel('marketplace/userprofile')->getCollection()
												->addFieldToFilter('mageuserid',array('eq'=>$sellerid));
		foreach($sellers as $seller){ $seller->delete(); }
		
		$sellerpro= Mage::getModel('marketplace/product')->getCollection()
							->addFieldToFilter('userid',array('eq'=>$sellerid));
		foreach($sellerpro as $pro){
			$allStores = Mage::app()->getStores();
			foreach ($allStores as $_eachStoreId => $val){
				Mage::getModel('catalog/product_status')->updateProductStatus($pro->getMageproductid(),Mage::app()->getStore($_eachStoreId)->getId(), Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
			}
			$pro->delete();
		}
	}

	public function CustomerRegister($observer){
	$data=Mage::getSingleton('core/app')->getRequest();
		if($data->getParam('wantpartner')==1){
			$customer = $observer->getCustomer();
			Mage::getModel('marketplace/userprofile')->getRegisterDetail($customer);
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerrequest');
			
			$emailTempVariables = array();
			$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
			$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
			$adminUsername = 'Admin';
			$emailTempVariables['myvar1'] = $customer->getName();
			$emailTempVariables['myvar2'] = Mage::getUrl('adminhtml/customer/edit', array('id' => $customer->getId()));
			
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			
			$emailTemp->setSenderName($customer->getName());
			$emailTemp->setSenderEmail($customer->getEmail());
			$emailTemp->send($adminEmail,$customer->getName(),$emailTempVariables);
		}
	}
	public function DeleteProduct($observer) { 
		$collection = Mage::getModel('marketplace/product')->getCollection()
														   ->addFieldToFilter('mageproductid',$observer->getProduct()->getId());
		foreach($collection as $data){			
			Mage::getModel('marketplace/product')->load($data['index_id'])->delete();			
		}		
	}
	
	public function afterPlaceOrder($observer) { 
		$lastOrderId=$observer->getOrder()->getId();
		$order = Mage::getModel('sales/order')->load($lastOrderId);
        //if($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING) {
            Mage::getModel('marketplace/saleslist')->getProductSalesCalculation($order);
        //}
	}
	
	
	public function commissionCalculationOnComplete($observer){
	    $order = $observer->getOrder();
	    if($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE){
	    	Mage::getModel('marketplace/saleslist')->getCommsionCalculation($order);
	    }
	}
	
	public function afterSaveCustomer($observer){
		$customer=$observer->getCustomer();
		$customerid=$customer->getId();
		$isPartner= Mage::getModel('marketplace/userprofile')->isPartner();
		if($isPartner==1){
			$data=$observer->getRequest();
			$sid = $data->getParam('sellerassignproid');
			$unassignproid = $data->getParam('sellerunassignproid');
			$partner_type = $data->getParam('partnertype');
			if($partner_type==2)
			{
				$collectionselectdelete = Mage::getModel('marketplace/userprofile')->getCollection();
				$collectionselectdelete->addFieldToFilter('mageuserid',array($customerid));
				foreach($collectionselectdelete as $delete){
					$autoid=$delete->getautoid();
				}
				$collectiondelete = Mage::getModel('marketplace/userprofile')->load($autoid);
				$collectiondelete->delete();
				$customer = Mage::getModel('customer/customer')->load($customerid);	
				$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerdisapprove');
			
				$emailTempVariables = array();		
				$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
				$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');	
				$adminUsername = 'Admin';
				$emailTempVariables['myvar1'] = $customer->getName();
				$emailTempVariables['myvar2'] = Mage::helper('customer')->getLoginUrl();
				
				$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
				
				$emailTemp->setSenderName($adminUsername);
				$emailTemp->setSenderEmail($adminEmail);
				$emailTemp->send($customer->getEmail(),$Username,$emailTempVariables);	
			}
			if($sid !=''||$sid!= 0){
				Mage::getModel('marketplace/userprofile')->assignProduct($customer,$sid);
			}
			if($unassignproid !=''||$unassignproid!= 0){
				Mage::getModel('marketplace/userprofile')->unassignProduct($customer,$unassignproid);
			}
			$wholedata=$data->getParams();
            $groups = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', $wholedata['member'])
                ->load();
            foreach($groups as $group){
                $commision = $group['transaction_percent'];
            }

			$collectionselect = Mage::getModel('marketplace/saleperpartner')->getCollection();
			$collectionselect->addFieldToFilter('mageuserid',array('eq'=>$customer->getId()));
			if(count($collectionselect)==1){
			    foreach($collectionselect as $verifyrow){
				$autoid=$verifyrow->getautoid();
				}
				
				$collectionupdate = Mage::getModel('marketplace/saleperpartner')->load($autoid);
				$collectionupdate->setcommision($commision);
				$collectionupdate->save();
				}
			else{
				$collectioninsert=Mage::getModel('marketplace/saleperpartner');
				$collectioninsert->setmageuserid($customer->getId());
				$collectioninsert->setcommision($commision);
				$collectioninsert->save();
			}

			/*Save seller info*/
			$collection = Mage::getModel('marketplace/userprofile')->getCollection();
			$collection->addFieldToFilter('mageuserid',array('eq'=>$customer->getId()));
			foreach($collection as  $value){ 
				$data = $value; 
				$value->settwitterid($wholedata['twitterid']);
				$value->setfacebookid($wholedata['facebookid']);
				$value->setcontactnumber($wholedata['contactnumber']);
				$value->setshoptitle($wholedata['shoptitle']);
				$value->setcomplocality($wholedata['complocality']);
				$value->setMetaKeyword($wholedata['meta_keyword']);
                $value->setareamobile($wholedata['areamobile']);

				if($wholedata['compdesi']){
					$wholedata['compdesi'] = str_replace('script', '', $wholedata['compdesi']);
				}
				$value->setcompdesi($wholedata['compdesi']);

				if($wholedata['returnpolicy']){
					$wholedata['returnpolicy'] = str_replace('script', '', $wholedata['returnpolicy']);
				}
				$value->setReturnpolicy($wholedata['returnpolicy']);

				if($wholedata['shippingpolicy']){
					$wholedata['shippingpolicy'] = str_replace('script', '', $wholedata['shippingpolicy']);
				}
				$value->setShippingpolicy($wholedata['shippingpolicy']);
				
				$value->setMetaDescription($wholedata['meta_description']);
				$target =Mage::getBaseDir().'/media/avatar/';
				if(strlen($_FILES['bannerpic']['name'])>0){
					$extension = pathinfo($_FILES["bannerpic"]["name"], PATHINFO_EXTENSION);
					$temp = explode(".",$_FILES["bannerpic"]["name"]);
                    $img1 = $temp[0].rand(1,99999).$loid.'.'.$extension;
					$value->setbannerpic($img1);
					$targetb = $target.$img1; 
					move_uploaded_file($_FILES['bannerpic']['tmp_name'],$targetb);
				}
				if(strlen($_FILES['logopic']['name'])>0){
					$extension = pathinfo($_FILES["logopic"]["name"], PATHINFO_EXTENSION);
					$temp1 = explode(".",$_FILES["logopic"]["name"]);
                    $img2 = $temp1[0].rand(1,99999).$loid.'.'.$extension;
					$value->setlogopic($img2);					
					$targetl = $target.$img2; 
					move_uploaded_file($_FILES['logopic']['tmp_name'],$targetl);
				}
				if (array_key_exists('countrypic', $wholedata)) {
					$value->setcountrypic($wholedata['countrypic']);
				}
                if (array_key_exists('countrymobile', $wholedata)) {
                    $value->setcountrymobile($wholedata['countrymobile']);
                }
                if (array_key_exists('member', $wholedata)) {
                    $value->setmember($wholedata['member']);
                }

				$value->save();
			}
		}
        else{
				$data=$observer->getRequest();
				$partner_type = $data->getParam('partnertype');
				$profileurl = $data->getParam('profileurl');
				$wholedata=$data->getParams();
				if($partner_type==1)
				{
					if($profileurl!=''){
						$profileurlcount = Mage::getModel('marketplace/userprofile')->getCollection();
						$profileurlcount->addFieldToFilter('profileurl',$profileurl);
                        $userid = -1;
                        foreach($profileurlcount as $profile){
                            $userid = $profile['mageuserid'];
                        }
						if($userid==$customer->getId() || count($profileurlcount)==0){
							$collectionselect = Mage::getModel('marketplace/userprofile')->getCollection();
							$collectionselect->addFieldToFilter('mageuserid',array($customer->getId()));
							if(count($collectionselect)>=1){
								foreach($collectionselect as $coll){
										$coll->setWantpartner('1');
										$coll->setpartnerstatus('Seller');
										$coll->setProfileurl($data->getParam('profileurl'));
										$coll->save();
								}
							}	
								else{
									$collection=Mage::getModel('marketplace/userprofile');
									$collection->setwantpartner(1);
									$collection->setpartnerstatus('Seller');
									$collection->setProfileurl($data->getParam('profileurl'));
									$collection->setmageuserid($customer->getId());
									$collection->save();
							}
							$customer = Mage::getModel('customer/customer')->load($customerid);

							$emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerapprove');
			
							$emailTempVariables = array();				
							$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
							$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
							$adminUsername = 'Admin';
							$emailTempVariables['myvar1'] = $customer->getName();
							$emailTempVariables['myvar2'] = Mage::helper('customer')->getLoginUrl();
							
							$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
							
							$emailTemp->setSenderName($adminUsername);
							$emailTemp->setSenderEmail($adminEmail);
							$emailTemp->send($customer->getEmail(),$Username,$emailTempVariables);

                            $customerno = $customer->getContactno();
                            $sellername = $customer->getShopname();
                            $selldes = $customer->getShopdescription();
                            $countryno = $customer->getCountryphone();
                            $areacode = $customer->getAreacode();

                            $countrynofix = '\'' .$countryno.'\'';
                            $areacodefix = '\'' .$areacode.'\'';

                            $sellernamefix = '\'' .$sellername.'\'';
                            $selldesf = '<div>' .$selldes.'</div>';
                            $selldesfix = '\'' .$selldesf.'\'';

                            $resource = Mage::getSingleton('core/resource');
                            $writeConnection = $resource->getConnection('core_write');
                            $table = 'marketplace_userdata';
                            $query = 'UPDATE ' . $table . ' SET shoptitle = ' . $sellernamefix . ' , areamobile = ' . $areacodefix . ' , countrymobile = ' . $countrynofix . ' , compdesi = '.$selldesfix.' , contactnumber = '.$customerno.' , member = 4 WHERE mageuserid = ' . $customerid;
                            $writeConnection->query($query);

                            $groups2 = Mage::getResourceModel('customer/group_collection')
                                ->addFieldToFilter('customer_group_id', '4')
                                ->load();
                            foreach($groups2 as $group2){
                                $limit2 = $group2['transaction_percent'];
                            }

                            $collectionselect = Mage::getModel('marketplace/saleperpartner')->getCollection();
                            $collectionselect->addFieldToFilter('mageuserid',array('eq'=>$customer->getId()));
                            if(count($collectionselect)==1){
                                foreach($collectionselect as $verifyrow){
                                    $autoid=$verifyrow->getautoid();
                                }

                                $collectionupdate = Mage::getModel('marketplace/saleperpartner')->load($autoid);
                                $collectionupdate->setcommision($limit2);
                                $collectionupdate->save();
                            }
                            else{
                                $collectioninsert=Mage::getModel('marketplace/saleperpartner');
                                $collectioninsert->setmageuserid($customer->getId());
                                $collectioninsert->setcommision($limit2);
                                $collectioninsert->save();
                            }



						} else {
							Mage::getSingleton('core/session')->addError('This Shop Name alreasy Exists.');
						}	
					}
					else{
						Mage::getSingleton('core/session')->addError('Enter Shop Url of Customer.');
					}
				}
			}
	}
	
	public function checkInvoiceSubmit($observer) { 
		$seller_items_array = array();
		$invoice_seller_ids = array();
		$event = $observer->getEvent()->getInvoice();
        Mage::log($event->getOrderId(), null, 'seller_order.log', true);
		foreach ($event->getAllItems() as $value) {
			$invoiceproduct = $value->getData();
			$pro_seller_id = 0;
			$product_seller	= Mage::getModel('marketplace/product')->getCollection()
					->addFieldToFilter('mageproductid',$invoiceproduct['product_id']);
			foreach ($product_seller as $sellervalue) {
				if($sellervalue->getUserid()){
					$invoice_seller_ids[$sellervalue->getUserid()] = $sellervalue->getUserid();
					$pro_seller_id = $sellervalue->getUserid();			
				}
                Mage::log($sellervalue->getUserid(), null, 'seller_ids.log', true);
			}
			if($pro_seller_id){
				$seller_items_array[$pro_seller_id][] = $invoiceproduct;
			}
            Mage::log($pro_seller_id, null, 'seller_ids2.log', true);
		}
		$order = Mage::getModel('sales/order')->load($event->getOrderId());
        Mage::log($invoice_seller_ids, null, 'seller_ids3.log', true);
		foreach($invoice_seller_ids as $invoice_seller_id){
            Mage::log($invoice_seller_id, null, 'seller_ids4.log', true);
			$fetchsale = Mage::getModel('marketplace/saleslist')->getCollection();
			$fetchsale->addFieldToFilter('mageorderid',$event->getOrderId());	
			$fetchsale->addFieldToFilter('mageproownerid',$invoice_seller_id);
			$totalprice ='';
			$totaltax_amount= 0;
			$orderinfo = '';
				$style='style="font-size:11px;padding:3px 9px;border-bottom:1px dotted #cccccc";';
				$tax="<tr><td ".$style."><h3>Tax</h3></td><td ".$style."></td><td ".$style."></td><td ".$style."></td></tr><tr>";
				$options="<tr><td ".$style."><h3>Product Options</h3></td><td ".$style."></td><td ".$style."></td><td ".$style."></td></tr><tr><td ".$style."><b>Options</b></td><td ".$style."><b>Value</b></td><td ".$style."></td><td ".$style."></td></tr>";		
			foreach($fetchsale as $res){
                Mage::log($res['mageproname'], null, 'saleslist.log', true);
				$orderinfo = $orderinfo."<tr>
								<td valign='top' align='left' ".$style." >".$res['mageproname']."</td>
								<td valign='top' align='left' ".$style.">".Mage::getModel('catalog/product')->load($res['mageproid'])->getSku()."</td>
								<td valign='top' align='left' ".$style." >".$res['magequantity']."</td>
								<td valign='top' align='left' ".$style.">".Mage::app()->getStore()->formatPrice($res['totalamount'])."</td>
							 </tr>";	
		
				foreach($order->getAllItems() as $item){
					if($item->getProductId()==$res['mageproid']){
						$taxPrice = $item->getTaxAmount();
						$totaltax_amount=$totaltax_amount + $item->getTaxAmount();
						$taxAmount=Mage::app()->getStore()->formatPrice($item->getTaxAmount());
						$tax=$tax."<tr><td ".$style."><b>Tax Amount</b></td><td ".$style."></td><td ".$style."></td><td ".$style.">".$taxAmount."</td></tr>";
						$temp=$item->getProductOptions();
						
						if (array_key_exists('options', $temp)) {
						foreach($temp['options'] as $data){
							$optionflag=1;
							$options=$options."<tr><td ".$style."><b>".$data['label']."</b></td><td ".$style.">".$data['value']."</td><td ".$style."></td><td ".$style."></td></tr>";
							}
						 }
						else {$optionflag='';}
							
					 }
				} 
				$totalprice = $totalprice+$res['totalamount'];
				$userdata = Mage::getModel('customer/customer')->load($res['mageproownerid']);				
				$Username = $userdata['firstname'];
				$useremail = $userdata['email'];			
			}
			$seller_info_array[$invoice_seller_id] = $userdata;
			
			$shipcharge = $order->getShippingAmount();
			if($totaltax_amount>0){
				$orderinfo=$orderinfo.$tax;
			}
			if($optionflag==1){
				$orderinfo=$orderinfo.$options;
			}

            // Tung fix GrandTotal

            $shipping_sellers = Mage::getModel('mpshippingmanager/tracking')->getCollection()
                ->addFieldToFilter('order_id',$event->getOrderId())
                ->addFieldToFilter('seller_id',$invoice_seller_id);
            $shipping_charges = 0;
            foreach($shipping_sellers as $shipping_seller){
                $shipping_charges = $shipping_charges + $shipping_seller['shipping_charges'];
            }
            // End

			$orderinfo = $orderinfo."</tbody><tbody><tr>
										<td align='right' style='padding:3px 9px' colspan='3'>Grandtotal</td>
										<td align='right' style='padding:3px 9px' colspan='3'><span>".Mage::app()->getStore()->formatPrice($totalprice + $totaltax_amount + $shipping_charges)."</span></td>
									</tr>";
					
			$billingId = $order->getBillingAddress()->getId();
			$billaddress = Mage::getModel('sales/order_address')->load($billingId);
			$billinginfo = $billaddress['firstname'].'<br/>'.$billaddress['street'].'<br/>'.$billaddress['city'].' '.$billaddress['region'].' '.$billaddress['postcode'].'<br/>'.Mage::getModel('directory/country')->load($billaddress['country_id'])->getName().'<br/>T:'.$billaddress['telephone'];	
			
			if($order->getShippingAddress()!='')
				$shippingId = $order->getShippingAddress()->getId();
			else
				$shippingId = $billingId;
			$address = Mage::getModel('sales/order_address')->load($shippingId);				
			$shippinginfo = $address['firstname'].'<br/>'.$address['street'].'<br/>'.$address['city'].' '.$address['region'].' '.$address['postcode'].'<br/>'.Mage::getModel('directory/country')->load($address['country_id'])->getName().'<br/>T:'.$address['telephone'];	
			
			$payment = $order->getPayment()->getMethodInstance()->getTitle();
			if($order->getShippingAddress()){
				$shippingId = $order->getShippingAddress()->getId();
                Mage::log($shippingId, null, 'seller_ids5.log', true);
				$address = Mage::getModel('sales/order_address')->load($shippingId);				
				$shippinginfo = $address['firstname'].'<br/>'.$address['street'].'<br/>'.$address['city'].' '.$address['region'].' '.$address['postcode'].'<br/>'.Mage::getModel('directory/country')->load($address['country_id'])->getName().'<br/>T:'.$address['telephone'];


                $shipping_sellers = Mage::getModel('mpshippingmanager/tracking')->getCollection()
                    ->addFieldToFilter('order_id',$event->getOrderId())
                    ->addFieldToFilter('seller_id',$invoice_seller_id);
                $custom_shipping_txt = '';
                foreach($shipping_sellers as $shipping_seller){
                    $custom_shipping_txt .= $shipping_seller['carrier_name'] . '</p>';
                }
                //Tung edit

                //$lastOrderId = $order->getId();
                //$shippingmethod = $order->getShippingMethod();
                /*$allorderitems = $order->getAllItems();
                $des = ($order->getShippingDescription());
                //if (strpos($shippingmethod, 'mp_multi_shipping') !== false) {
                    $shippingAll = Mage::getSingleton('core/session')->getData('selected_shipping');
                    $custom_shipping_txt = ' ';//to save custom shipping info to orders - Phuc
                    foreach ($shippingAll as $sellerid => $shipdata) {
                        $_seller = Mage::getModel('customer/customer')->load($sellerid);
                        Mage::log($_seller->getId(), null, 'seller_id6.log', true);
                        $custom_shipping_txt .= '<p class="' . $_seller->getId() . '">';
                        $items = explode(',', $shipdata['items']);
                        $shipdata['items'] = "";
                        foreach ($allorderitems as $item) {
                            if (in_array($item->getQuoteItemId(), $items)) {
                                $shipdata['items'] = $shipdata['items'] . $item->getItemId() . ",";
                            }
                        }
                        Mage::log($invoice_seller_id, null, 'seller_id7.log', true);
                        Mage::log($sellerid, null, 'seller_id8.log', true);
                        if($invoice_seller_id == $sellerid){
                            $custom_shipping_txt .= ' ' . $_seller->getFirstname() . ' ' . $_seller->getLastname() . ' : ' . $shipdata['method'] . '</p>';
                        }
                    }


                    $shipping = $custom_shipping_txt;
                    $shippingd = $shipping;
                    Mage::log($shippingd, null, 'seller_id9.log', true);
                    //}*/

                //End Tung

                $shipping = $custom_shipping_txt;
                //$shipping = $order->getShippingDescription();
				$shippinfo = $shippinginfo;
				$shippingd = $shipping;
                Mage::log($shippingd, null, 'seller_ids_result.log', true);
			}
		
			$emailTemp = Mage::getModel('core/email_template')->loadDefault('webkulorderinvoice');
			
			$emailTempVariables = array();				
			$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
			$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
			$adminUsername = Mage::getStoreConfig('trans_email/ident_general/name');
			$emailTempVariables['myvar1'] = $res['magerealorderid'];
			$emailTempVariables['myvar2'] = $res['cleared_at'];
			$emailTempVariables['myvar4'] = $billinginfo;
			$emailTempVariables['myvar5'] = $payment;
			$emailTempVariables['myvar6'] = $shippinfo;
			$emailTempVariables['myvar9'] = $shippingd;
			$emailTempVariables['myvar8'] = $orderinfo;
			$emailTempVariables['myvar3'] =$Username;
			
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			
			$emailTemp->setSenderName($adminUsername);
			$emailTemp->setSenderEmail($adminEmail);
			$emailTemp->send($useremail,$Username,$emailTempVariables);
		}
		Mage::dispatchEvent('mp_product_sold',array('itemwithseller'=>$seller_items_array));
	}		
}
