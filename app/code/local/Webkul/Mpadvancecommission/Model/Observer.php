<?php
class Webkul_Mpadvancecommission_Model_Observer
{

	public function setcommission($observer)
	{
        $product_id = $observer->getData();
		$pid= $product_id ['id'];
		$loadpro =Mage::getModel('catalog/product')->load($pid);
		$prcommission = $loadpro->getCommissionForProduct();
		if($prcommission==''){
			$comar=array();
			$categry= Mage::getModel('catalog/category');
		    foreach($loadpro->getCategoryIds() as $key){
			  array_push($comar, $categry->load($key)->getCommissionForAdmin()); 
			}
			
			if(count($comar)){ $prcommission= max($comar); }
		}
		
		Mage::getSingleton('core/session')->setData('commission',$prcommission);
	}
		
}
