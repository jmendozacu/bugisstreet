
<?php
class Webkul_Mpshippingmanager_Block_Invoice extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		return parent::_prepareLayout();
    }
	
	public function getOrderDetail(){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$orderslist=Mage::getModel('marketplace/saleslist')->getCollection()
										 ->addFieldToFilter('mageproownerid',array('eq'=>$customerid))
										 ->addFieldToFilter('mageproid',array('eq'=>$this->getRequest()->getParam('id')))
										 ->setOrder('mageorderid','DESC');
	    return $orderslist;
	}
	
	public function getOrderItems(){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$orderslist=Mage::getModel('marketplace/saleslist')->getCollection()
										 ->addFieldToFilter('mageproownerid',array('eq'=>$customerid))
										 ->addFieldToFilter('mageorderid',array('eq'=>$this->getRequest()->getParam('id')))
										 ->setOrder('mageorderid','DESC');
		return $orderslist;
	}
	
	public function getTrackingNumber($orderid){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$trackingsdata=Mage::getModel('mpshippingmanager/tracking')->getCollection()
												 ->addFieldToFilter('order_id',array('eq'=>$orderid))
												 ->addFieldToFilter('seller_id',array('eq'=>$customerid));
		foreach($trackingsdata as $tracking){
			return $tracking;//->getTrackingnumber();
		}
	
	}
	
	public function getItemStatus($orderid,$itemid){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$trackingsdata=Mage::getModel('mpshippingmanager/tracking')->getCollection()
												 ->addFieldToFilter('order_id',array('eq'=>$orderid))
												 ->addFieldToFilter('seller_id',array('eq'=>$customerid));
		foreach($trackingsdata as $tracking){
			return $tracking->getItemOrderStatus();
		}
	}
 
}
