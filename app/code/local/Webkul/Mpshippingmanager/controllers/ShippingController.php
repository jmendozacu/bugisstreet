<?php
	require_once 'Mage/Customer/controllers/AccountController.php';
	
	class Webkul_Mpshippingmanager_ShippingController extends Mage_Customer_AccountController
	{
		public function indexAction(){
			$this->loadLayout();  
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('mpshippingmanager')->__('Manage Shipping'));
			$this->renderLayout();	
		}

		public function selleraddressAction(){
			if($this->getRequest()->isPost()){
				if(!$this->_validateFormKey()){
					 $this->_redirect('*/*/');
				}
				$wholedata=$this->getRequest()->getParams();
				$customerid=Mage::getSingleton('customer/session')->getCustomerId();

				$collection = Mage::getModel('marketplace/userprofile')->getCollection();
				$collection->addFieldToFilter('mageuserid',array('eq'=>$customerid));
				foreach($collection as $row){
					$id=$row->getAutoid();
				}

				$collectionload = Mage::getModel('marketplace/userprofile')->load($id);
				$collectionload->setOthersInfo($wholedata['others_info']);
				$collectionload->save();
				Mage::getSingleton('core/session')->addSuccess( Mage::helper('marketplace')->__('Information has been saved'));				
			}
			$this->_redirect('*/*/');
		}

		public function printAction()
	    {
	        if ($shipmentId = $this->getRequest()->getParam('invoice_id')) { // invoice_id o_0
	            if ($shipment = Mage::getModel('sales/order_shipment')->load($shipmentId)) {
	                $pdf = Mage::getModel('mpshippingmanager/order_pdf_shipment')->getPdf(array($shipment));
	                $this->_prepareDownloadResponse('packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
	            }
	        }
	        else {
	            $this->_forward('noRoute');
	        }
	    }

	    public function printallAction()
	    {
	    	$get=$this->getRequest()->getParams();
			$todate = date_create($get['special_to_date']);
			$to = date_format($todate, 'Y-m-d H:i:s');
			$fromdate = date_create($get['special_from_date']);
			$from = date_format($fromdate, 'Y-m-d H:i:s');

			$shipmentIds = array();

			$collection = Mage::getModel('marketplace/saleslist')->getCollection()
							->addFieldToFilter('mageproownerid', array('eq' => Mage::getSingleton('customer/session')->getCustomerId()))
							->addFieldToFilter('cleared_at', array('datetime' => true,'from' => $from,'to' =>  $to))
							->addFieldToSelect('mageorderid')
							->distinct(true);
			foreach($collection as $coll){				
				$shipping_coll = Mage::getModel('mpshippingmanager/tracking')->getCollection()
								->addFieldToFilter('order_id',array('eq'=>$coll->getMageorderid()))
								->addFieldToFilter('seller_id',array('eq'=>Mage::getSingleton('customer/session')->getCustomerId()));
				foreach ($shipping_coll as $tracking) {
					if($tracking->getShipmentId()){
						array_push($shipmentIds, $tracking->getShipmentId());
					}
				}
			}

	        if (!empty($shipmentIds)) {
	            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
	                ->addAttributeToSelect('*')
	                ->addAttributeToFilter('entity_id', array('in' => $shipmentIds))
	                ->load();
	            if (!isset($pdf)){
	                $pdf = Mage::getModel('mpshippingmanager/order_pdf_shipment')->getPdf($shipments);
	            } else {
	                $pages = Mage::getModel('mpshippingmanager/order_pdf_shipment')->getPdf($shipments);
	                $pdf->pages = array_merge ($pdf->pages, $pages->pages);
	            }

	            return $this->_prepareDownloadResponse('packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
	        }
	        $this->_redirect('marketplace/marketplaceaccount/myorderhistory');
	    }

	    public function printinvoiceAction()
	    {
	        if ($invoice_id = $this->getRequest()->getParam('invoice_id')) { 
	            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoice_id)) {	            	
	                $pdf = Mage::getModel('mpshippingmanager/order_pdf_invoice')->getPdf(array($invoice));
	                $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
	                    '.pdf', $pdf->render(), 'application/pdf');
	            }
	        }
	        else {
	            $this->_forward('noRoute');
	        }
	    }

	    public function printallinvoiceAction()
	    {
	    	$get=$this->getRequest()->getParams();
			$todate = date_create($get['special_to_date']);
			$to = date_format($todate, 'Y-m-d H:i:s');
			$fromdate = date_create($get['special_from_date']);
			$from = date_format($fromdate, 'Y-m-d H:i:s');

			$invoiceIds = array();

			$collection = Mage::getModel('marketplace/saleslist')->getCollection()
							->addFieldToFilter('mageproownerid', array('eq' => Mage::getSingleton('customer/session')->getCustomerId()))
							->addFieldToFilter('cleared_at', array('datetime' => true,'from' => $from,'to' =>  $to))
							->addFieldToSelect('mageorderid')
							->distinct(true);
			foreach($collection as $coll){				
				$shipping_coll = Mage::getModel('mpshippingmanager/tracking')->getCollection()
								->addFieldToFilter('order_id',array('eq'=>$coll->getMageorderid()))
								->addFieldToFilter('seller_id',array('eq'=>Mage::getSingleton('customer/session')->getCustomerId()));
				foreach ($shipping_coll as $tracking) {
					if($tracking->getInvoiceId()){
						array_push($invoiceIds, $tracking->getInvoiceId());
					}
				}
			}

	        if (!empty($invoiceIds)) {
	            $invoice = Mage::getResourceModel('sales/order_invoice_collection')
	                ->addAttributeToSelect('*')
	                ->addAttributeToFilter('entity_id', array('in' => $invoiceIds))
	                ->load();
	            if (!isset($pdf)){
	                $pdf = Mage::getModel('mpshippingmanager/order_pdf_invoice')->getPdf($invoice);
	            } else {
	                $pages = Mage::getModel('mpshippingmanager/order_pdf_invoice')->getPdf($invoice);
	                $pdf->pages = array_merge ($pdf->pages, $pages->pages);
	            }

	            return $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
	        }
	        $this->_redirect('marketplace/marketplaceaccount/myorderhistory');
	    }

		public function cancelorderAction(){
			try{
				$orderid=$this->getRequest()->getParam('id');
				$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
				$collection = Mage::getModel('marketplace/saleslist')->getCollection()
								->addFieldToFilter('mageproownerid', array('eq' => $partnerid))
								->addFieldToFilter('mageorderid', array('eq' => $orderid));
				$flag=false;
				foreach($collection as $saleproduct){
					$saleproduct->setCpprostatus(2);
					$saleproduct->save();
					$trackingcoll = Mage::getModel('mpshippingmanager/tracking')->getCollection()
								->addFieldToFilter('order_id',array('eq'=>$orderid))
								->addFieldToFilter('seller_id',array('eq'=>$partnerid));
					foreach($trackingcoll as $tracking){
							$tracking->setTrackingNumber('canceled');
							$tracking->setCarrierName('canceled');
							$tracking->save();	
							$flag=true;
					}
					
				}
				$trackingcol=Mage::getModel('mpshippingmanager/tracking')->getCollection()
									->addFieldtoFilter('order_id',array('eq'=>$orderid))
									->addFieldtoFilter('tracking_number',array('eq'=>''));	
				if(count($trackingcol)==0){
						$trackingcol=Mage::getModel('mpshippingmanager/tracking')->getCollection()
									->addFieldtoFilter('order_id',array('eq'=>$orderid))
									->addFieldtoFilter('tracking_number',array('eq'=>'canceled'));
						if(count($trackingcol)==0){
							$order = Mage::getModel('sales/order')->load($orderid);
							$product_order_id = $order->getIncrementId();
							$order->setStatus('complete');
							$order->save();
						}else{
							$order = Mage::getModel('sales/order')->load($orderid);
							$order->cancel();
							$order->save();
						}	
					}
				if($flag==true){
					Mage::getSingleton('core/session')->addSuccess('Order Cancled successfully..');
				}else{
					Mage::getSingleton('core/session')->addError('You are not permitted for cancel this order..');
				}
			}catch(Exception $e){
				Mage::getSingleton('core/session')->addError($e->getMessage());
			}
			$this->_redirect('mpshippingmanager/shipping/invoice/id/'.$orderid);
		}
		
		public function saveTrackingNumberAction(){
			try{
				if($this->getRequest()->isPost()){
					$orderid=$this->getRequest()->getParam('order_id');
					$trackingid=$this->getRequest()->getParam('tracking_id');
					$carrier=$this->getRequest()->getParam('carrier');
                    if($trackingid==''){
                        $trackingid='Not Applicable';
                    }
					$order=Mage::getModel('sales/order')->load($orderid);
					$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
					$items=array();
					$shippingAmount=0;
					$trackingsdata=Mage::getModel('mpshippingmanager/tracking')->getCollection()
													 ->addFieldToFilter('order_id',array('eq'=>$orderid))
													 ->addFieldToFilter('seller_id',array('eq'=>$partnerid));
					$product=Mage::getModel('catalog/product')->load($itemid);
					foreach($trackingsdata as $tracking){
						$tracking->setTrackingNumber($trackingid);
						$tracking->setCarrierName($carrier);
						$tracking->save();
						$product_name="";
						$shippingAmount=$tracking->getShippingCharges();
						$items=explode(',',$tracking->getItemIds());
						foreach($order->getAllItems() as $item){
							if(in_array($item->getId(),$items)){
								$product_name =$product_name.$product->getName()." ,";
							}
						}
						
						$product_name = $product->getName();
						$to = $order->getCustomerEmail();

						$emailTemp = Mage::getModel('core/email_template')->loadDefault('trackingnopartner');
						$emailTempVariables = array();
						$adminEmail=Mage::getStoreConfig('trans_email/ident_general/email');
						$emailTempVariables['myvar1'] = $order->getIncrementId();
						$emailTempVariables['myvar2'] = $product_name;
						$emailTempVariables['myvar3'] = $order->getIncrementId();
						$emailTempVariables['myvar4'] = $trackingid;
						$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
						$emailTemp->setSenderName('Admin');
						$emailTemp->setSenderEmail($adminEmail);
						$emailTemp->send($to,'Admin',$emailTempVariables);
					}

						/**/
					$itemsarray = $this->_getItemQtys($order,$items);
					
					if(count($itemsarray)>0){
						if($order->canInvoice()) { 
							$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($itemsarray['data']);
							$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
							$invoice->setShippingAmount($shippingAmount);
							$invoice->setSubtotal($itemsarray['subtotal']);
							$invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
							$invoice->setGrandTotal($itemsarray['subtotal']+$shippingAmount);
							$invoice->setBaseGrandTotal($itemsarray['subtotal']+$shippingAmount);
							$invoice->register();
						
							$transactionSave = Mage::getModel('core/resource_transaction')
										->addObject($invoice)
										->addObject($invoice->getOrder());
							$transactionSave->save();
							$order->addStatusHistoryComment(
								Mage::helper('mpshippingmanager')->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
							)
							->setIsCustomerNotified(true)
							->save();
							$seller_invoice_id = $invoice->getId();
						}else{
							if($order->hasInvoices()) {
							    foreach ($order->getInvoiceCollection() as $inv) { 
							        foreach ($inv->getAllItems() as $item) { 
							            $product_id = $item->getProductId();
							            $seller_pro_coll=Mage::getModel('marketplace/product')->getCollection()
					            								->addFieldToFilter('mageproductid',array('eq'=>$product_id))
					            								->addFieldToFilter('userid',array('eq'=>$partnerid));
					            		foreach ($seller_pro_coll as $value) {
					            			if($value->getMageproductid()){
					            				$seller_invoice_id = $inv->getIncrementId();
					            				break;
					            			}
					            		}
							        }
							    }
							}
						}

												
						$shipment = false;				
						$shipmentId = $this->getRequest()->getParam('shipment_id');			
						$orderId = $orderid;	
						if($shipmentId){
							$shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
						}elseif($orderId){
							$order  = Mage::getModel('sales/order')->load($orderId);
							if (!$order->getId()) {
								$this->_getSession()->addError($this->__('The order no longer exists.'));
								return false;
							}
							if($order->getForcedDoShipmentWithInvoice()){
								$this->_getSession()->addError($this->__('Cannot do shipment for the order separately from invoice.'));
								return false;
							}
							if(!$order->canShip()){
								$this->_getSession()->addError($this->__('Cannot do shipment for the order.'));
								return false;
							}

							$savedQtys = $this->_getItemQtys($order,$items);
							$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys['data']);
						}
						$shipment->register();
						$comment = '';
						$shipment->getOrder()->setCustomerNoteNotify(1);
						$responseAjax = new Varien_Object();
						$isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
						if ($isNeedCreateLabel && true) {
							$responseAjax->setOk(true);
						}
						$shipment->getOrder()->setIsInProcess(true);
						$transactionSave = Mage::getModel('core/resource_transaction')
									->addObject($shipment)->addObject($shipment->getOrder())->save();
						/*sale list status update*/
						$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
						$collection = Mage::getModel('marketplace/saleslist')->getCollection()
									->addFieldToFilter('mageproownerid', array('eq' => $partnerid))
									->addFieldToFilter('mageorderid', array('eq' => $orderid));
						foreach($collection as $saleproduct){
							$saleproduct->setCpprostatus(1);
							$saleproduct->save();
						}
						/*sale list status update*/
						$shipment->sendEmail(1, $comment);
						$shipmentCreatedMessage = $this->__('The shipment has been created.');
						$labelCreatedMessage    = $this->__('The shipping label has been created.');
						$this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
							: $shipmentCreatedMessage);	
						
						$trackingcol1=Mage::getModel('mpshippingmanager/tracking')->getCollection()
									->addFieldtoFilter('order_id',array('eq'=>$orderid))
									->addFieldtoFilter('seller_id',array('in'=>$partnerid));
						$courrier="custom";
						foreach($trackingcol1 as $row) {
							if($shipment->getId() != '') { 
								$row->setShipmentId($shipment->getId());
								$row->setInvoiceId($seller_invoice_id)->save();
								$track = Mage::getModel('sales/order_shipment_track')
								 ->setShipment($shipment)
								 ->setData('title',  $row->getCarrierName())
								 ->setData('number', $row->getTrackingNumber())
								 ->setData('carrier_code',  $courrier)
								 ->setData('order_id', $shipment->getData('order_id'))
								 ->save();
							}
						}
					}
					$trackingcol=Mage::getModel('mpshippingmanager/tracking')->getCollection()
									->addFieldtoFilter('order_id',array('eq'=>$orderid))
									->addFieldtoFilter('tracking_number',array('eq'=>''));
					if(count($trackingcol)==0){
						$trackingcol=Mage::getModel('mpshippingmanager/tracking')->getCollection()
									->addFieldtoFilter('order_id',array('eq'=>$orderid))
									->addFieldtoFilter('tracking_number',array('eq'=>'canceled'));
						$order = Mage::getModel('sales/order')->load($orderid);
						if(count($trackingcol)==0){
							$product_order_id = $order->getIncrementId();
							$order->setStatus('complete');
							$order->save();
						}else{
							$order->cancel();
							$order->save();
						}
						//$order->sendNewOrderEmail();
						$historyItem = Mage::getResourceModel('sales/order_status_history_collection')
							->getUnnotifiedForInstance($order, Mage_Sales_Model_Order::HISTORY_ENTITY_NAME);
						if ($historyItem) {
							$historyItem->setIsCustomerNotified(1);
							$historyItem->save();
						}		
					}
					if(count($trackingsdata)==0)
						Mage::getSingleton('core/session')->addError("This order doesn't contain Tracking Number..");
					else
						Mage::getSingleton('core/session')->addSuccess('Tracking Number successfully Assign..');
					$this->_redirect('mpshippingmanager/shipping/invoice/id/',array('id'=>$orderid));
				}
			}catch(Exception $e){
				Mage::getSingleton('core/session')->addError($e->getMessage());
				$this->_redirect('mpshippingmanager/shipping/invoice/id/',array('id'=>$orderid));
			}
		}
		
		/*Related to invoice from seller end*/
		public function salesdetailAction(){
			$this->loadLayout( array('default','marketplace_account_salesdetail'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle($this->__('MarketPlace: Salesdetail of Seller Product'));
			$this->renderLayout();
		}

		public function invoiceAction(){
			$this->loadLayout( array('default','marketplace_account_invoice'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle($this->__('MarketPlace: Invoice of Seller Order'));
			$this->renderLayout();
		}
		
		public function getTrackingNumber($orderid){
			$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
			$trackingsdata=Mage::getModel('mpshippingmanager/tracking')->getCollection()
							->addFieldToFilter('order_id',array('eq'=>$orderid))
							->addFieldToFilter('seller_id',array('eq'=>$partnerid));
			foreach($trackingsdata as $tracking){
				return $tracking->getTrackingNumber();
			}
		}

		protected function _getItemQtys($order,$items){
			$data=array();
			$subtotal = 0;
			$baseSubtotal = 0;
			foreach($order->getAllItems() as $item){
				if(in_array($item->getItemId(),$items)){
					$data[$item->getItemId()]=intval($item->getQtyOrdered());
					$subtotal+=$item->getRowTotal();
					$baseSubtotal+=$item->getBaseRowTotal();
				}else{
					$data[$item->getItemId()]=0;
				}   
			}
			return array('data'=>$data,'subtotal'=>$subtotal,'basesubtotal'=>$baseSubtotal);
		}
	}
