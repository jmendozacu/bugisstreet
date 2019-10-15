<?php
require_once 'Mage/Customer/controllers/AccountController.php';
class Webkul_Mpmultishipmethod_IndexController extends Mage_Customer_AccountController{
    public function IndexAction() {
      if($this->getRequest()->isPost()){
		  if(!$this->_validateFormKey()) {
				return $this->_redirect('marketplace/marketplaceaccount/simpleproduct/');
            }
			$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
			$customer=Mage::getModel('customer/customer')->load($partnerid);
			$customer->setAllowedShipping(json_encode($this->getRequest()->getParam('shippingmethod')));
			$customer->save();
			$this->_getSession()
				->addSuccess(Mage::helper('mpmultishipmethod')->__('Your allowed shipping has been successfully Saved'));		
			$this->_redirect('mpshippingmanager/shipping');
		}	
	  
    }
}
