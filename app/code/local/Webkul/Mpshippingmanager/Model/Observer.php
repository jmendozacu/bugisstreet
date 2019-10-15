<?php

Class Webkul_Mpshippingmanager_Model_Observer
{
	public function afterPlaceOrderStatus($observer){
		$status=Mage::getStoreConfig('marketplace/marketplace_options/order_approval');
		if($status==0){
			$lastOrderId=$observer->getOrderIds();
			$order = Mage::getModel('sales/order')->load($lastOrderId)->setStatus('processing')->save();
		}	
	}
		
}
