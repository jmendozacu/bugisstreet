<?php  
    class Webkul_Mpmultishipmethod_Model_Carrier_Mpmultishipping     
		extends Mage_Shipping_Model_Carrier_Abstract
		//implements Mage_Shipping_Model_Carrier_Interface
	{  
        protected $_code = 'mp_multi_shipping';  
      
        /** 
        * Collect rates for this shipping method based on information in $request 
        * 
        * @param Mage_Shipping_Model_Rate_Request $data 
        * @return Mage_Shipping_Model_Rate_Result 
        */  
        public function collectRates(Mage_Shipping_Model_Rate_Request $request){
			 if(!Mage::getStoreConfig('carriers/'.$this->_code.'/active')){
					return false;
			}
			$result = Mage::getModel('shipping/rate_result');
			$handling = 0;
			$session = Mage::getSingleton('checkout/session');
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$postcode=$session->getQuote()->getShippingAddress()->getPostcode();
			$countrycode=$session->getQuote()->getShippingAddress()->getCountry();
			$postcode=str_replace('-', '', $postcode);
			$shippingdetail=array();
			$shippostaldetail=array('countrycode'=>$countrycode,'postalcode'=>$postcode);
			foreach ($session->getQuote()->getAllVisibleItems() as $item) {
				$proid=$item->getProductId();
				$options=$item->getProductOptions();
	            $mpassignproductId=$options['info_buyRequest']['mpassignproduct_id'];
	            if(!$mpassignproductId) {
	                foreach($item->getOptions() as $option) {
	                    $temp=unserialize($option['value']);
	                    if($temp['mpassignproduct_id']) {
	                        $mpassignproductId=$temp['mpassignproduct_id'];
	                    }
	                }
	            }
	            if($mpassignproductId) {
	                $mpassignModel = Mage::getModel('mpassignproduct/mpassignproduct')->load($mpassignproductId);
	                $partner = $mpassignModel->getSellerId();
	            } else {
					$collection=Mage::getModel('marketplace/product')
						->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$proid));
					foreach($collection as $temp) {
						$partner=$temp->getUserid();
					}
	            }
				$product=Mage::getModel('catalog/product')->load($proid)->getWeight();
				$weight=$product*$item->getQty();
				if(count($shippingdetail)==0){
					array_push($shippingdetail,array('seller_id'=>$partner,'items_weight'=>$weight,'product_name'=>$item->getName()." X ".$item->getQty(),'qty'=>$item->getQty(),'item_id'=>$item->getId()));
				}else{
					$shipinfoflag=true;
					$index=0;
					foreach($shippingdetail as $itemship){
						if($itemship['seller_id']==$partner){
							$itemship['items_weight']=$itemship['items_weight']+$weight;
							$itemship['product_name']=$itemship['product_name'].",".$item->getName()." X ".$item->getQty();
							$itemship['qty']=$itemship['qty']+$item->getQty();
							$itemship['item_id']=$itemship['item_id'].",".$item->getId();
							$shippingdetail[$index]=$itemship;
							$shipinfoflag=false;
						}
						$index++;
					}
					if($shipinfoflag==true){
						array_push($shippingdetail,array('seller_id'=>$partner,'items_weight'=>$weight,'product_name'=>$item->getName()." X ".$item->getQty(),'qty'=>$item->getQty(),'item_id'=>$item->getId()));
					}
				}
			}
			
			$allowedshipping=new Webkul_Mpmultishipmethod_Block_Index();
			$allowedshipping=$allowedshipping->getAllCarriersList();
			$shipmethodarr=array(
				'webkulshipping'=>Mage::getModel('mpshipping/carrier_LocalDelivery'),
				'webkulshippingcanadapostal'=>Mage::getModel('mpshippingcanadapostal/carrier_LocalDelivery'),
				'webkulshippingfixrate'=>Mage::getModel('mpshippingfixrate/carrier_LocalDelivery'),
				'mpupsshipping'=>Mage::getModel('mpupsshipping/carrier_LocalDelivery'),
				'mpuspsshipping'=>Mage::getModel('mpuspsshipping/carrier_LocalDelivery'),
				'mpfedexshipping'=>Mage::getModel('mpfedexshipping/carrier_LocalDelivery'),
				'mparamexshipping'=>Mage::getModel('mparamexshipping/carrier_LocalDelivery'),
				'mpperproductshipping'=>Mage::getModel('mpperproductshipping/carrier_LocalDelivery'),
				'mpshippingcorreios'=>Mage::getModel('mpshippingcorreios/carrier_LocalDelivery'),
				'mppercountryperproductshipping'=>Mage::getModel('mppercountryperproductshipping/carrier_LocalDelivery'),
				'mpfastwayshipping'=>Mage::getModel('mpfastwayshipping/carrier_LocalDelivery'),
			);
			$shipaccordingtoseller=array();
			foreach($allowedshipping as $shippinginfo){
				$shippingpricedetail=$shipmethodarr[$shippinginfo['value']]->getShippingPricedetail($shippingdetail,$shippostaldetail);
					
				foreach($shippingpricedetail['shippinginfo'] as $shipdata){
					if(isset($shipaccordingtoseller[$shipdata['seller_id']])){
						if(isset($shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']])){
							$submethod=$shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']];
							foreach($shipdata['submethod'] as $submethoddetail){
								array_push($submethod,$submethoddetail);
							}
							$shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']]=$submethod;
						}else{
							$shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']]=$shipdata['submethod'];
							$shipaccordingtoseller[$shipdata['seller_id']]['products']=$shipdata['product_name'];
							$shipaccordingtoseller[$shipdata['seller_id']]['item_ids']=$shipdata['item_ids'];
						}
					}else{
						//$shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']]=array(array('method'=>'','cost'=>$shipdata['shipping_ammount']));
						$shipaccordingtoseller[$shipdata['seller_id']][$shipdata['methodcode']]=$shipdata['submethod'];
						$shipaccordingtoseller[$shipdata['seller_id']]['products']=$shipdata['product_name'];
						$shipaccordingtoseller[$shipdata['seller_id']]['item_ids']=$shipdata['item_ids'];
					}
				}
				
			}
	
			$descreption="";
			foreach($shipaccordingtoseller as $seller_id=>$infoseller){
				$seller="<div class='seller'><div class='name'>".Mage::getModel('customer/customer')->load($seller_id)->getName()."<div class='products'>".$infoseller['products']."</div></div>";
				$seller=$seller."<input class='selectedshippingname' name='selected_shipping[".$seller_id."][method]' type='hidden' value='' />";
				$seller=$seller."<input class='selectedshippingitems' name='selected_shipping[".$seller_id."][items]' type='hidden' value='".$infoseller['item_ids']."' />";
				$seller=$seller."<input class='selectedshippingamount' name='selected_shipping[".$seller_id."][amount]' type='hidden' value='' />";
				$sellerdata=Mage::getModel('customer/customer')->load($seller_id)->getAllowedShipping();
				$sellerdata=json_decode($sellerdata,true);
				$shipavlflag=false;
				foreach($infoseller as $shipmethod=>$methoddetailarr){
					if(!in_array($shipmethod,$sellerdata)){continue;}
					$shipavlflag=true;
					$methods="<ul class='form-list'>";
					foreach($methoddetailarr as $methoddetail){
						if($methoddetail['error']==1){continue;}
						$cost=Mage::helper('core')->currency($methoddetail['cost'], true, false);
						$method="<li><input type='radio' class='custom_ship' name='".$seller_id."' value='".$methoddetail['cost']."'></input><label class='mpmultishiplabel'>".$methoddetail['method']." <span class='price'>".$cost."</span></label></li>";
						$methods=$methods.$method;
					}
					$methods=$methods."</ul>";
					$seller=$seller.$methods;
				}
				if($shipavlflag===false){
					$seller=$seller."<p class='noshipping'>".Mage::helper('mpmultishipmethod')->__('Sorry, no quotes are available for these items at this time from vendor.')."</p>";
				}
				$seller=$seller."</div>";
				$descreption=$descreption.$seller;
			}
			
		/*****/
		$shippingamount=0;	
		$custm= Mage::getSingleton('core/session')->getMpMultiShippingAmt();
			if($custm!=""){
				$shippingamount=floatval($custm);
			}
            $result = Mage::getModel('shipping/rate_result');  
            $method = Mage::getModel('shipping/rate_result_method');  
            $method->setCarrier($this->_code);  
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($this->_code);  
            $method->setMethodTitle($this->getConfigData('name'));
		    $method->setPrice($shippingamount);
			$method->setCost($shippingamount);
			$method->setMethodDescription($descreption);
            $result->append($method);
            return $result;  
        }  

		/**
		 * Get allowed shipping methods
		 *
		 * @return array
		 */
		public function getAllowedMethods()
		{
			return array($this->_code=>$this->getConfigData('name'));
		}
		
		
		
		public function saveShippingMethod($observer){
			$data=$observer->getRequest();
			$shippingamount=$data->getParam('multicustomship');
			$selected_shipping=$data->getParam('selected_shipping');
			$quote=$observer->getQuote();
			$data=$quote->getShippingAddress()->getAllShippingRates();
			foreach($data as $_rate){
				if($_rate->getCode()=='mp_multi_shipping_mp_multi_shipping'){
					$_rate->setPrice($shippingamount);
					$_rate->save();
					Mage::getSingleton('core/session')->setMpMultiShippingAmt($shippingamount);
					Mage::getSingleton('core/session')->setSelectedShipping($selected_shipping);
					$quote->save();
				}
			}
		}

		public function afterPlaceOrder($observer) { 
			$lastOrderId=$observer->getOrderIds();
			$order = Mage::getModel('sales/order')->load($lastOrderId);
			$lastOrderId=$order->getId();
			$shippingmethod=$order->getShippingMethod();
			$allorderitems=$order->getAllItems();
			if(strpos($shippingmethod,'mp_multi_shipping')!==false){
				$shippingAll=Mage::getSingleton('core/session')->getData('selected_shipping');
				foreach($shippingAll as $sellerid=>$shipdata){
					$items=explode(',',$shipdata['items']);
					$shipdata['items']="";
					foreach($allorderitems as $item){
					   if(in_array($item->getQuoteItemId(),$items)){
							$shipdata['items']=$shipdata['items'].$item->getItemId().",";
					   }
					}
					$data=array(
								'order_id'=>$lastOrderId,
								'item_ids'=>$shipdata['items'],
								'seller_id'=>$sellerid,
								'carrier_name'=>$shipdata['method'],
								'shipping_charges'=>$shipdata['amount']
							);
					$collectiont=Mage::getModel('mpshippingmanager/tracking');
					$collectiont->setData($data);
					$collectiont->save();
				}	
				Mage::getSingleton('core/session')->unsetSelectedShipping();	
			}			
		}
    }  
