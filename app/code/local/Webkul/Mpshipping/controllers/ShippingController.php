<?php
require_once 'Mage/Customer/controllers/AccountController.php';
class Webkul_Mpshipping_ShippingController extends Mage_Customer_AccountController
{
    public function indexAction(){
		if($this->getRequest()->isPost()){
			$file=$_FILES['shippingfile']['tmp_name'];		
			$file_handle = fopen($file, "r");	
			$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();	
			$count=0;
			//var_dump($file_handle);die;
			while(!feof($file_handle)){
				$row = fgetcsv($file_handle, 1024);
				var_dump($row,$count);
				if($row[0]==''||$row[2]==''||$row[3]==''||$count==0){
					$count++;				
					continue;		
				}else
				{
					$row[1]=($row[1]=='')?'*':$row[1];
					//var_dump('hello');
					$temp=array(		
					'dest_country_id'=>$row[0],		
					'dest_region_id'=>$row[1],	
					'dest_zip'=>$row[2],
					'dest_zip_to'=>$row[3],					
					'price'=>$row[4],		
					'weight_from'=>$row[5],	
					'weight_to'=>$row[6],
					'partner_id'=>$partnerid,	
					);		
					$collection=Mage::getModel('mpshipping/mpshipping')->getCollection()
								->addFieldToFilter('dest_country_id',array('eq'=>$row[0]))
								->addFieldToFilter('dest_region_id',array('eq'=>$row[1]))
								->addFieldToFilter('dest_zip',array('eq'=>$row[2]))
								->addFieldToFilter('dest_zip_to',array('eq'=>$row[3]))
								->addFieldToFilter('weight_from',array('eq'=>$row[5]))
								->addFieldToFilter('weight_to',array('eq'=>$row[6]))
								->addFieldToFilter('partner_id',array('eq'=>$partnerid));
					if(count($collection)>0){
						foreach($collection as $data){
							$data->setPrice($row[4]);
							$data->save();
						}
					}
					else{
						$mpshipping=Mage::getModel('mpshipping/mpshipping');
						$mpshipping->setData($temp);
						$mpshipping->save();
					}
				}
			}
			//die;
			fclose($file_handle);	
			$this->_getSession()
				->addSuccess(Mage::helper('mpshipping')->__('Your shipping detail has been successfully Saved'));		
			$this->_redirect('mpshippingmanager/shipping');
		}else{
			$this->loadLayout();  
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('mpshipping')->__('Manage Shipping'));
			$this->renderLayout();
		}	
    }
	/*public function cancelorderAction(){
		$orderid=$this->getRequest()->getParam('id');
		$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
		$collection = Mage::getModel('marketplace/saleslist')->getCollection()
						->addFieldToFilter('mageproownerid', array('eq' => $partnerid))
						->addFieldToFilter('mageorderid', array('eq' => $orderid));
		$flag=false;
		foreach($collection as $saleproduct){
			$saleproduct->setCpprostatus(2);
			$saleproduct->save();
			$trackingcoll = Mage::getModel('mpshipping/tracking')->getCollection()
						->addFieldToFilter('order_id',array('eq'=>$orderid))
						->addFieldToFilter('seller_id',array('eq'=>$partnerid));
			foreach($trackingcoll as $tracking){
					$tracking->setTrackingNumber('canceled');
					$tracking->setCarrierName('canceled');
					$tracking->save();	
					$flag=true;
			}
			
		}
		$trackingcol=Mage::getModel('mpshipping/tracking')->getCollection()
							->addFieldtoFilter('order_id',array('eq'=>$orderid))
							->addFieldtoFilter('tracking_number',array('eq'=>''));	
		if(count($trackingcol)==0){
				$trackingcol=Mage::getModel('mpshipping/tracking')->getCollection()
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
		$this->_redirect('mpshipping/shipping/invoice/id/'.$orderid);
		
	}
	
	public function saveTrackingNumberAction(){
		if($this->getRequest()->isPost()){
			$orderid=$this->getRequest()->getParam('order_id');
			$trackingid=$this->getRequest()->getParam('tracking_id');
			$carrier=$this->getRequest()->getParam('carrier');
			$order=Mage::getModel('sales/order')->load($orderid);
			$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
			$items=array();
			$shippingAmount=0;
			$trackingsdata=Mage::getModel('mpshipping/tracking')->getCollection()
											 ->addFieldToFilter('order_id',array('eq'=>$orderid))
											 ->addFieldToFilter('seller_id',array('eq'=>$partnerid));
			$product=Mage::getModel('catalog/product')->load($itemid);
			foreach($trackingsdata as $tracking){
				$tracking->setTrackingNumber($trackingid);
				//$tracking->setItemOrderStatus('complete');
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
					$order->sendNewOrderEmail()->addStatusHistoryComment(
						Mage::helper('mpshipping')->__('Notified customer about invoice #%s.', $invoice->getId())
					)
					->setIsCustomerNotified(true)
					->save();
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
				/*sale list status update*
				$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
				$collection = Mage::getModel('marketplace/saleslist')->getCollection()
							->addFieldToFilter('mageproownerid', array('eq' => $partnerid))
							->addFieldToFilter('mageorderid', array('eq' => $orderid));
				foreach($collection as $saleproduct){
					$saleproduct->setCpprostatus(1);
					$saleproduct->save();
				}
				/*sale list status update*
				$shipment->sendEmail(1, $comment);
				$shipmentCreatedMessage = $this->__('The shipment has been created.');
				$labelCreatedMessage    = $this->__('The shipping label has been created.');
				$this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
					: $shipmentCreatedMessage);	
				
				$trackingcol1=Mage::getModel('mpshipping/tracking')->getCollection()
							->addFieldtoFilter('order_id',array('eq'=>$orderid))
							->addFieldtoFilter('seller_id ',array('in'=>$partnerid));
				$courrier="webkul";
				foreach($trackingcol1 as $row) {
					if($shipment->getId() != '') { 
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
			
			$trackingcol=Mage::getModel('mpshipping/tracking')->getCollection()
							->addFieldtoFilter('order_id',array('eq'=>$orderid))
							->addFieldtoFilter('tracking_number',array('eq'=>''));
			if(count($trackingcol)==0){
				$trackingcol=Mage::getModel('mpshipping/tracking')->getCollection()
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
				$order->sendNewOrderEmail();
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
			$this->_redirect('mpshipping/shipping/invoice/id/'.$orderid);
		}
	
	}*/
	
	
	/*public function producttrackingnumberAction(){
		$trackingdata = $this->getRequest()->getParam('tracking');
		$orderid = $this->getRequest()->getParam('orderid');
		$itemid = $this->getRequest()->getParam('itemid');
		$trackingsdata=Mage::getModel('mpshipping/tracking')->getCollection()
											 ->addFieldToFilter('orderid',array('eq'=>$orderid))
											 ->addFieldToFilter('itemid',array('eq'=>$itemid));
		$product=Mage::getModel('catalog/product')->load($itemid);
		$order=Mage::getModel('sales/order')->load($orderid);
		foreach($trackingsdata as $tracking){
			$tracking->setTrackingnumber($trackingdata);
			$tracking->save();
			$product_name = $product->getName();
			$to = $order->getCustomerEmail();


			$emailTemp = Mage::getModel('core/email_template')->loadDefault('trackingnopartner');
			$emailTempVariables = array();				
			$adminEmail=Mage::getStoreConfig('trans_email/ident_general/email');
			$emailTempVariables['myvar1'] = $order->getIncrementId();
			$emailTempVariables['myvar2'] = $product_name;
			$emailTempVariables['myvar3'] = $order->getIncrementId();
			$emailTempVariables['myvar4'] = $trackingdata;
			$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
			$emailTemp->setSenderName('Admin');
			$emailTemp->setSenderEmail($adminEmail);
			$emailTemp->send($to,'Admin',$emailTempVariables);
		}
		
		$trackingcol=Mage::getModel('mpshipping/tracking')->getCollection()
							->addFieldtoFilter('orderid',array('eq'=>$orderid))
							->addFieldtoFilter('trackingnumber',array('eq'=>''));		
		
		if(count($trackingcol)){
			echo json_encode("processing");
		}else{
			$order = Mage::getModel('sales/order')->load($orderid);
			$product_order_id = $order->getIncrementId();
			$order->setStatus('complete');
			$order->save();
			echo json_encode("complete");
		}
	}*/
	
	/*Related to invoice from seller end*/
	/*public function salesdetailAction(){
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

	public function downloadallshipAction(){
		$get=$this->getRequest()->getParams();
		if (!is_dir($get['dir'].'/download/'.$get['id'].'/')){
			mkdir($get['dir'].'/download/'.$get['id'].'/',0755);
		}
		$curr = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();		
		
		require($get['dir']."/skin/frontend/default/default/marketplace/mpshipping/fpdf.php");	
		
		$data = Mage::getModel('marketplace/saleslist')
				->getCollection()
				->addFieldToFilter('mageproownerid', array('in' => $get['id']))
				->addFieldToFilter('mageorderid', array('in' => $get['orderid']))
				->load()
				->toArray();
		$magerealorder = $data['items'][0]['magerealorderid'];
		$mageproid = $data['items'][0]['mageproid'];
		$cleared_at = $data['items'][0]['cleared_at'];
		$cleareddate = date_create($cleared_at);
		$cleareddatenew = date_format($cleareddate, 'd/m/Y h:i A');		
		
		$_order=Mage::getModel('sales/order')->load($get['orderid']);
		$addrdetail = explode(",",$_order->getBillingAddress()->format());	
			
		$_pAddsses = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
		$selleraddrdetail = explode(",",Mage::getSingleton('customer/session')->getCustomer()->getAddressById($_pAddsses)->format());
		$selleraddr = Mage::getSingleton('customer/session')->getCustomer()->getAddressById($_pAddsses);
		
		$header = array('Order No.','Product Name', 'Price', 'Qty','Total Amount');				
		$pdf = new FPDF();
		$grand=$pdf->GrandT($data);
		$pdf->SetFont('Arial','',12);
		$pdf->AddPage();
		
		$pdf->SetFont('Arial','B',20);
		$pdf->SetTextColor(228,132,22);
		$pdf->Cell(190,10,'#'.$magerealorder,'',1,'L');
		
		$pdf->SetFont('Arial','B',15);
		$pdf->SetTextColor(228,132,22);
		$pdf->Cell(190,2,'','LTR',1,'L',0);
		$pdf->Cell( 190, 7, 'Ship To :   ','LR', 1,'L' );
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell(190,5,'   '.$addrdetail[0],'LR',1,'L',0);
		$pdf->Cell(190,5,'  '.$addrdetail[1],'LR',1,'L',0);
		$pdf->Cell(190,5,'  '.$addrdetail[2].','.$addrdetail[3].','.$addrdetail[4],'LR',1,'L',0);
		$pdf->Cell(190,5,'','LR',1,'L',0);
		
		$pdf->SetFont('Arial','',12);
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell( 190, 7, 'Shipping No. : '.$this->getTrackingNumber($get['orderid']).'                                                                    Shipping Date :'.$cleareddatenew,'LTR', 1,'L' );
		
		$pdf->SetFont('Arial','',12);
		$pdf->title('Items to Invoice',$get['id']);
		//$pdf->BasicHeader($header);
		$pdf->BasicData($data,$curr);
		
		$pdf->SetTextColor(0,0,255);
		$pdf->SetFont('Arial','B',15);
		$pdf->Cell( 190, 8, 'GrandTotal :   '.$curr.$grand, 'LTRB', 1,'R' );				
		
		$pdf->SetFont('Arial','',12);
		$pdf->SetTextColor(228,132,22);
		$pdf->Cell( 190, 7, 'Shipped By :   ', 'LR', 1,'L' );
		
		$pdf->SetTextColor(0,0,0);
		$pdf->Cell(190,5,'   '.$selleraddrdetail[0],'LR',1,'L',0);
		$pdf->Cell(190,5,'   '.$selleraddr->getCompany(),'LR',1,'L',0);
		$pdf->Cell(190,5,'  '.$selleraddrdetail[1],'LR',1,'L',0);
		$pdf->Cell(190,5,'  '.$selleraddrdetail[2].','.$selleraddrdetail[3].','.$selleraddrdetail[4],'LR',1,'L',0);
		$pdf->Cell(190,5,'','LRB',1,'L',0);
		
		$pdf->Output($get['dir'].'/download/'.$get['id'].'/'.$get['orderid'].'.pdf','F');
		echo json_encode(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'/download/'.$get['id'].'/'.$get['orderid'].".pdf");
	}
	
	public function downloadallshipbydateAction(){
		$flag=0;
		$pendingfiles=array();
		$get=$this->getRequest()->getParams();
		$todate = date_create($get['todate']);
		$to = date_format($todate, 'Y-m-d H:i:s');
		$fromdate = date_create($get['fromdate']);
		$from = date_format($fromdate, 'Y-m-d H:i:s');
		
		$curr = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		
		require($get['dir']."/skin/frontend/default/default/marketplace/mpshipping/fpdf.php");	
		
		$collection = Mage::getModel('marketplace/saleslist')
				->getCollection()
				->addFieldToFilter('mageproownerid', array('in' => $get['id']))
				->addFieldToFilter('cleared_at', array('datetime' => true,'from' => $from,'to' =>  $to))
				->addFieldToSelect('mageorderid')
				->distinct(true);
		foreach($collection as $coll){
			$data = Mage::getModel('marketplace/saleslist')
				->getCollection()
				->addFieldToFilter('mageproownerid', array('in' => $get['id']))
				->addFieldToFilter('mageorderid', array('in' => $coll['mageorderid']))
				->load()
				->toArray();
				
			$magerealorder = $data['items'][0]['magerealorderid'];
			$mageproid = $data['items'][0]['mageproid'];
			$cleared_at = $data['items'][0]['cleared_at'];
			$cleareddate = date_create($cleared_at);
			$cleareddatenew = date_format($cleareddate, 'd/m/Y h:i A');
			
			$_order=Mage::getModel('sales/order')->load($coll['mageorderid']);
			if($_order->getStatus()=='complete'){
			
				if (!is_dir($get['dir'].'/download/date/')){
					mkdir($get['dir'].'/download/date/',0755);
				}
				if (!is_dir($get['dir'].'/download/date/'.$get['id'].'/')){
					mkdir($get['dir'].'/download/date/'.$get['id'].'/',0755);
				}
				$addrdetail = explode(",",$_order->getBillingAddress()->format());	
			
				$_pAddsses = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
				$selleraddrdetail = explode(",",Mage::getSingleton('customer/session')->getCustomer()->getAddressById($_pAddsses)->format());
				$selleraddr = Mage::getSingleton('customer/session')->getCustomer()->getAddressById($_pAddsses);
				
				$header = array('Order No.','Product Name', 'Price', 'Qty','Total Amount');				
				$pdf = new FPDF();
				$grand=$pdf->GrandT($data);
				$pdf->SetFont('Arial','',12);
				$pdf->AddPage();
				
				$pdf->SetFont('Arial','B',20);
				$pdf->SetTextColor(228,132,22);
				$pdf->Cell(190,10,'#'.$magerealorder,'',1,'L');
				
				$pdf->SetFont('Arial','B',15);
				$pdf->SetTextColor(228,132,22);
				$pdf->Cell(190,2,'','LTR',1,'L',0);
				$pdf->Cell( 190, 7, 'Ship To :   ','LR', 1,'L' );
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(190,5,'   '.$addrdetail[0],'LR',1,'L',0);
				$pdf->Cell(190,5,'  '.$addrdetail[1],'LR',1,'L',0);
				$pdf->Cell(190,5,'  '.$addrdetail[2].','.$addrdetail[3].','.$addrdetail[4],'LR',1,'L',0);
				$pdf->Cell(190,5,'','LR',1,'L',0);
				
				$pdf->SetFont('Arial','',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell( 190, 7, 'Shipping No. : '.$this->getTrackingNumber($coll['mageorderid']).'                                                                    Shipping Date :'.$cleareddatenew,'LTR', 1,'L' );
				
				$pdf->SetFont('Arial','',12);
				$pdf->title('Items to Invoice',$get['id']);
				$pdf->BasicHeader($header);
				$pdf->BasicData($data,$curr);
				
				$pdf->SetTextColor(0,0,255);
				$pdf->SetFont('Arial','B',15);
				$pdf->Cell( 190, 8, 'GrandTotal :   '.$curr.$grand, 'LTRB', 1,'R' );				
				
				$pdf->SetFont('Arial','',12);
				$pdf->SetTextColor(228,132,22);
				$pdf->Cell( 190, 7, 'Shipped By :   ', 'LR', 1,'L' );
				
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(190,5,'   '.$selleraddrdetail[0],'LR',1,'L',0);
				$pdf->Cell(190,5,'   '.$selleraddr->getCompany(),'LR',1,'L',0);
				$pdf->Cell(190,5,'  '.$selleraddrdetail[1],'LR',1,'L',0);
				$pdf->Cell(190,5,'  '.$selleraddrdetail[2].','.$selleraddrdetail[3].','.$selleraddrdetail[4],'LR',1,'L',0);
				$pdf->Cell(190,5,'','LRB',1,'L',0);
				
				$pdf->Output($get['dir'].'/download/date/'.$get['id'].'/'.$coll['mageorderid'].'.pdf','F');
				$flag=1;
			}else{
				if($magerealorder){
					array_push($pendingfiles,"#".$magerealorder);
					$magerealorder='';
				}
			}
		}
		$files = implode($pendingfiles,',');
		if($flag==1){
			$path=$get['dir'].'/download/date/'.$get['id'];
			$dir = $path;
			$zip = new ZipArchive();
			$zip->open('download/date/'.$get['id'].".zip", ZipArchive::CREATE);
			$zip->addFromString('tmp', $get['dir'].'/download/date/');
			$zip->close();		
			$this->Zip($get['dir'].'/download/date/'.$get['id'], $get['dir'].'/download/date/'.$get['id'].".zip");	
			if (is_dir($path)) { 
				$objects = scandir($path); 
				foreach ($objects as $object) { 
					if ($object != "." && $object != "..") { 
						if (filetype($path."/".$object) == "dir"){
							deleteDirectory($path."/".$object);
						}else{
							unlink($path."/".$object);
						} 
					} 
					reset($objects); 
					rmdir($path); 
				} 
			}
			
			echo json_encode(array(url=>Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'/download/date/'.$get['id'].".zip",pendingfiles=>$files));
		}else{			
			echo json_encode(array(msg=>'No order exist',pendingfiles=>$files));
		}
	}
	
	public function Zip($source, $destination)	{
		if (!extension_loaded('zip') || !file_exists($source)) {
			return false;
		}
		$zip = new ZipArchive();
		if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
			return false;
		}
		$source = str_replace('\\', '/', realpath($source));
		if (is_dir($source) === true)	{
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($files as $file){
				$file = str_replace('\\', '/', $file);
				if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
					continue;
				$file = realpath($file);
				if (is_dir($file) === true)	{
					$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
				}else if (is_file($file) === true)	{
					$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
				}
			}
		}else if (is_file($source) === true){
			$zip->addFromString(basename($source), file_get_contents($source));
		}
		return $zip->close();
	}
	
	public function getTrackingNumber($orderid){
		$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
		$trackingsdata=Mage::getModel('mpshipping/tracking')->getCollection()
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
			if(in_array($item->getId(),$items)){
				$data[$item->getId()]=intval($item->getQtyOrdered());
				$subtotal+=$item->getRowTotal();
				$baseSubtotal+=$item->getBaseRowTotal();
			}else{
				$data[$item->getId()]=0;
			}   
       	}
       	return array('data'=>$data,'subtotal'=>$subtotal,'basesubtotal'=>$baseSubtotal);
    }*/
}
