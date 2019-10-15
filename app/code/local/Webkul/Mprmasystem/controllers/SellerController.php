<?php
require_once "Mage/Customer/controllers/AccountController.php";
class Webkul_Mprmasystem_SellerController extends Mage_Customer_AccountController    {
    
    public function allrmaAction() {
        $this->loadLayout(array("default","seller_allrma"));
        $this->getLayout()->getBlock("head")->setTitle(Mage::helper("mprmasystem")->__("My Requested RMA"));
        $this->renderLayout();
    }

    public function viewrmaAction() {
        $this->loadLayout(array("default","seller_viewrma"));
        $this->getLayout()->getBlock("head")->setTitle(Mage::helper("mprmasystem")->__("Requested RMA Details"));
        $this->renderLayout();
    }

    protected function changestatusAction(){    	
    	$data = $this->getRequest()->getPost();
    	if($data){    		
        	$id = $this->getRequest()->getParam("rmaid");
            $sellerstatus = $this->getRequest()->getParam("sellerstatus");
            $model = Mage::getModel("mprmasystem/rmarequest")->load($id);
            $customerid = $model->getCustomerid();
            $sellerid = $model->getSellerid();
            $model->setSellerstatus($sellerstatus);
            if($sellerstatus == 0)
                Mage::getModel("mprmasystem/rmastatus")->setPending($id);
            if($sellerstatus == 1 || $sellerstatus == 2){
                //Mage::getModel("mprmasystem/rmastatus")->setProcessing($id);
                $model->setStatus("Processing")->save();
                if($sellerstatus == 2){
                    $model->setSellerUpdate(Mage::getModel('core/date')->date('Y-m-d H:i:s'))->save();
                }

            }

            if($sellerstatus == 3)
                Mage::getModel("mprmasystem/rmastatus")->setCancelbyseller($id);
            $model->setStatus($status);
            $rmaid = $model->save()->getId();
            if($rmaid > 0){
                Mage::getModel("mprmasystem/rmamail")->SellerStatusMail($sellerid,$sellerstatus,$id,$customerid);
            }
        	Mage::getSingleton("core/session")->addSuccess(Mage::helper("mprmasystem")->__("Congratulations Your RMA status has been successfully updated"));
	    	$this->_redirect("*/seller/viewrma/", array("id" => $id));
    	}
    	else{
    		Mage::getSingleton("core/session")->addNotice(Mage::helper("mprmasystem")->__("Sorry something went wrong"));
	    	$this->_redirect("*/*/");
    	}
    }

    protected function saveconversationAction(){
        	$data = $this->getRequest()->getParams();
            $customer = Mage::getSingleton("customer/session")->getCustomer();
        	$model = Mage::getModel("mprmasystem/conversation");
        	$model->setRmaid($data["rmaid"]);
        	$model->setMessage($data["message"]);
        	$model->setCreatedAt(time());
        	$model->setSender($customer->getName());   
        	$model->setSendertype($data['sendertype']);   
        	$msgid = $model->save()->getId();
            $rmarequestmodel = Mage::getModel("mprmasystem/rmarequest")->load($data["rmaid"]);
            $customerid = $rmarequestmodel->getCustomerid();
            if($msgid > 0)
                Mage::getModel("mprmasystem/rmamail")->SellerToCustomerMail($customer->getId(),$data["rmaid"],$customerid);
        	Mage::getSingleton("core/session")->addSuccess(Mage::helper("mprmasystem")->__("Message Saved Successfully"));
	    	$this->_redirect("*/seller/viewrma/", array("id" => $data["rmaid"]));
        }
}