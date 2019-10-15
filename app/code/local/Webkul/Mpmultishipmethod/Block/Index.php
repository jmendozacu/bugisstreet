<?php 
  
class Webkul_Mpmultishipmethod_Block_Index extends Mage_Core_Block_Template{

	public function getAllCarriersList(){
		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
		$options = array();
		foreach($methods as $_code => $_method){
			if(!$_title = Mage::getStoreConfig("carriers/$_code/title"))
				$_title = $_code;
				if((strpos($_code,'webkul') !== false || strpos($_code,'mp') !== false) && strpos($_code,'mp_multi_shipping') !== 0) {
					$options[] = array('value' => $_code, 'label' => $_title);//" ($_code)"
				}
				
		}
		return $options;
	}
	
	public function getAllowedShipping(){
			return Mage::getSingleton('customer/session')->getCustomer()->getAllowedShipping();
	}



}
