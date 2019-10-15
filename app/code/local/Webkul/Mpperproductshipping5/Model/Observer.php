<?php

Class Webkul_Mpperproductshipping5_Model_Observer
{
	public function afterPlaceOrder($observer) { 
		$lastOrderId=$observer->getOrderIds();
		$order = Mage::getModel('sales/order')->load($lastOrderId);
		$lastOrderId=$order->getId();
		$shippingmethod=$order->getShippingMethod();
		if(strpos($shippingmethod,'mpperproductshipping5')!==false){
			$allorderitems=$order->getAllItems();
			$shipmethod=explode('_',$shippingmethod);
			$shippingAll=Mage::getSingleton('core/session')->getData('shippinginfo');
			foreach($shippingAll['mpperproductshipping5'] as $shipdata){
				$items=explode(',',$shipdata['item_ids']);
				$shipdata['item_ids']="";
				foreach($allorderitems as $item){
				   if(in_array($item->getQuoteItemId(),$items)){
						$shipdata['item_ids']=$shipdata['item_ids'].$item->getItemId().",";
				   }
				}
				$data=array(
							'order_id'=>$lastOrderId,
							'item_ids'=>$shipdata['item_ids'],
							'seller_id'=>$shipdata['seller_id'],
							'carrier_name'=>$shipdata['submethod'][0]['method'],
							'shipping_charges'=>$shipdata['submethod'][0]['cost']
						);
				$collectiont=Mage::getModel('mpshippingmanager/tracking');
				$collectiont->setData($data);
				$collectiont->save();
			}
			Mage::getSingleton('core/session')->unsetShippinginfo();	
		}			
	}
}
