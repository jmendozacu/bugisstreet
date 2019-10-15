<?php
class Webkul_Mpshippingmanager_Model_Tracking extends Mage_Core_Model_Abstract
{
    protected function _construct(){
       $this->_init("mpshippingmanager/tracking");
    }

    public function getShippingPrice($order_id) {
    	$sellerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
    	$collection = Mage::getModel("mpshippingmanager/tracking")->getCollection()
    							->addFieldToFilter('order_id',array('eq'=>$order_id))
    							->addFieldToFilter('seller_id',array('eq'=>$sellerId));

    	foreach ($collection as $value) {
    		return $value->getShippingCharges();
    	}
    }

    public function getShippingPrice2($order_id, $sellerId) {
        //$sellerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $collection = Mage::getModel("mpshippingmanager/tracking")->getCollection()
            ->addFieldToFilter('order_id',array('eq'=>$order_id))
            ->addFieldToFilter('seller_id',array('eq'=>$sellerId));

        foreach ($collection as $value) {
            return $value->getCarrierName();
        }
    }
} 
