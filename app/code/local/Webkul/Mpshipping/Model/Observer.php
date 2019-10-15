<?php

Class Webkul_Mpshipping_Model_Observer
{
	public function afterPlaceOrder($observer) { 
		$lastOrderId=$observer->getOrderIds();
		$order = Mage::getModel('sales/order')->load($lastOrderId);
		$lastOrderId=$order->getId();
		$shippingmethod=$order->getShippingMethod();
		if(strpos($shippingmethod,'webkulshipping')!==false){
			if(strpos($shippingmethod,'webkulshippingfixrate')===false ||strpos($shippingmethod,'webkulshippingfixrate')!=0){
				$allorderitems=$order->getAllItems();
				$shippingAll=Mage::getSingleton('core/session')->getData('shippinginfo');
				foreach($shippingAll['webkulshipping'] as $shipdata){
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
	public function hookToControllerActionPostDispatch($observer)
	{
		//we compare action name to see if that's action for which we want to add our own event
		if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_add')
		{
			//We are dispatching our own event before action ADD is run and sending parameters we need
			Mage::dispatchEvent("add_to_cart_after", array('request' => $observer->getControllerAction()->getRequest()));
		}
	}

	public function hookToAddToCartAfter($observer)
	{
		//Hooking to our own event
		$request = Mage::app()->getRequest()->getParams();
		$cart = $observer->getQuoteItem();
		//var_dump($cart->getData());die;
		$cart->setData('custom_shipping_method',$request['custom_shipping_method']);
		//$cart->save();
	//	Mage::getSingleton('core/session')::set
		// do something with product
		//Mage::log("Product ".$request['product']." is added to cart.");
	}
}
