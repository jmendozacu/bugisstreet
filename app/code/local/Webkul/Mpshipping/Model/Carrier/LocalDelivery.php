<?php

class Webkul_Mpshipping_Model_Carrier_LocalDelivery extends Mage_Shipping_Model_Carrier_Abstract
{
    /* Use group alias */
    protected $_code = 'webkulshipping';
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        // skip if not enabled

        if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active') || (Mage::getStoreConfig('carriers/mp_multi_shipping/active'))) {
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
			if(count($shippingdetail)==0) {
				array_push($shippingdetail,array('seller_id'=>$partner,'items_weight'=>$weight,'product_name'=>$item->getName(),'item_id'=>$item->getId()));
			} else {
				$shipinfoflag=true;
				$index=0;
				foreach($shippingdetail as $itemship) {
					if($itemship['seller_id']==$partner){
						$itemship['items_weight']=$itemship['items_weight']+$weight;
						$itemship['product_name']=$itemship['product_name'].",".$item->getName();
						$itemship['item_id']=$itemship['item_id'].",".$item->getId();
						$shippingdetail[$index]=$itemship;
						$shipinfoflag=false;
					}
					$index++;
				}
				if($shipinfoflag==true) {
					array_push($shippingdetail,array('seller_id'=>$partner,'items_weight'=>$weight,'product_name'=>$item->getName(),'item_id'=>$item->getId()));
				}
			}
		}
		$shippingpricedetail=$this->getShippingPricedetail($shippingdetail,$shippostaldetail);
		
		if($shippingpricedetail['errormsg']!=="") {
			Mage::getSingleton('core/session')->setShippingCustomError($shippingpricedetail['errormsg']);
			return $result;
		}
		/*store shipping in session*/
		$shippingAll=Mage::getSingleton('core/session')->getData('shippinginfo');
		$shippingAll[$this->_code]=$shippingpricedetail['shippinginfo'];
		Mage::getSingleton('core/session')->setData('shippinginfo',$shippingAll);
		/*store shipping in session*/
		
        $method = Mage::getModel('shipping/rate_result_method');
		$method->setCarrier($this->_code);
		$method->setCarrierTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/title'));
        /* Use method name */
		$method->setMethod($this->_code);
	    $method->setMethodTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/name'));
		$method->setCost($shippingpricedetail['handlingfee']);
		$method->setPrice($shippingpricedetail['handlingfee']); 
        $result->append($method);
        return $result;	
    }
    
    public function getShippingPricedetail($shippingdetail,$shippostaldetail) {
		$submethod=array();
		$shippinginfo=array();
		$msg="";
		$handling=0;
		foreach($shippingdetail as $shipdetail){
			$price=0;
			$shipping=Mage::getModel('mpshipping/mpshipping')
										->getCollection()
										->addFieldToFilter('dest_country_id',array('eq'=>$shippostaldetail['countrycode']))
										->addFieldToFilter('partner_id',array('eq'=>$shipdetail['seller_id']))
										->addFieldToFilter('dest_zip',array('lteq'=>$shippostaldetail['postalcode']))		
										->addFieldToFilter('dest_zip_to',array('gteq'=>$shippostaldetail['postalcode']))
										->addFieldToFilter('weight_from',array('lteq'=>$shipdetail['items_weight']))
										->addFieldToFilter('weight_to',array('gteq'=>$shipdetail['items_weight']));
			if(count($shipping)==0){
				$shipping=Mage::getModel('mpshipping/mpshipping')
										->getCollection()
										->addFieldToFilter('dest_country_id',array('eq'=>$shippostaldetail['countrycode']))
										->addFieldToFilter('partner_id',array('eq'=>$shipdetail['seller_id']))
										->addFieldToFilter('dest_zip',array('eq'=>'*'))		
										->addFieldToFilter('dest_zip_to',array('eq'=>'*'))
										->addFieldToFilter('weight_from',array('lteq'=>$shipdetail['items_weight']))
										->addFieldToFilter('weight_to',array('gteq'=>$shipdetail['items_weight']));
			}
			foreach($shipping as $ship){
				$price=$ship->getPrice();
				$submethod=array(array('method'=>Mage::getStoreConfig('carriers/'.$this->_code.'/title'),'cost'=>$price,'error'=>0));
			}
			if(count($shipping)==0){
				$msg="<span style='color:#ff0000;'>Seller Of Product <u>".$shipdetail['product_name']."</u> Not Provide Shipping Service in Your Location</span>";
				$submethod=array(array('method'=>Mage::getStoreConfig('carriers/'.$this->_code.'/title'),'cost'=>$price,'error'=>1));
			}
			 $handling= $handling+$price;
			
			 array_push($shippinginfo,array('seller_id'=>$shipdetail['seller_id'],'methodcode'=>$this->_code,'shipping_ammount'=>$price,'product_name'=>$shipdetail['product_name'],'submethod'=>$submethod,'item_ids'=>$shipdetail['item_id']));
		}
		
		return array('handlingfee'=>$handling,'shippinginfo'=>$shippinginfo,'errormsg'=>$msg);
	}
    
}
 
