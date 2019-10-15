<?php
	require_once "Mage/Customer/controllers/AccountController.php";
	class Webkul_Mprmasystem_IndexController extends Mage_Customer_AccountController	{
		
		public function indexAction() {
			$this->loadLayout(array("default","mprmasystem_account"));
			$this->getLayout()->getBlock("head")->setTitle(Mage::helper("mprmasystem")->__("RMA"));
			$this->renderLayout();
		}

		public function newAction(){
			$this->loadLayout(array("default","mprmasystem_new"));
			$this->getLayout()->getBlock("head")->setTitle(Mage::helper("mprmasystem")->__("File Your Returns"));
			$this->renderLayout();
		}

        public function getorderdetailsAction(){
            $wholedata = $this->getrequest()->getPost();
            $order = Mage::getModel("sales/order")->load($wholedata["id"]);
            $Allitems = $order->getAllItems();
            $order_details = array();
            foreach ($Allitems as $item){
                if($item->getProductType() != 'configurable'){
                    $model = Mage::getModel("mprmasystem/rmarequest")->getCollection();
                    $model->addFieldToFilter('orderid',$wholedata["id"]);
                    $model->addFieldToFilter('itemid',$item->getProductId());
                    $availablerma = 1;
                    foreach ($model as $key) {
                        if($key->getId())
                            $availablerma = 0;
                    }
                    array_push($order_details,array("name" => $item->getName(),"sku" => $item->getSku(),"qty" => intval($item->getQtyOrdered()),"itemid" => $item->getProductId(),"availablerma" => $availablerma));
                }

            }
            echo json_encode($order_details);
        }

        public function savermaAction(){
        	$data = $this->getRequest()->getPost();
        	$error = false;
        	if($data){
        		$productowner = "";
        		$mpassignproductId = "";
        		$orderItem = Mage::getModel('sales/order_item')->getCollection()
        						->addFieldToFilter('product_id',$data["wk_rs_pro_check"])
        						->addFieldToFilter('order_id',$data["order"]);
        		foreach ($orderItem as $item) {
        			$options=$item->getProductOptions();
        			$mpassignproductId = $options['info_buyRequest']['mpassignproduct_id'];
        		}
        		if($mpassignproductId) {
        			$mpassignModel=Mage::getModel('mpassignproduct/mpassignproduct')->load($mpassignproductId);
        			$productowner = $mpassignModel->getSellerId();
        		} else {
                    $product=Mage::getModel('catalog/product')->load($data["wk_rs_pro_check"]);
                    $productType =$product->getTypeID();
                    $productowner = Mage::getModel('marketplace/product')->isCustomerProduct($data["wk_rs_pro_check"]);
                    if($productowner['userid']==""){
                        if($productType=="simple"){
                            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                                ->getParentIdsByChild($data["wk_rs_pro_check"]);
                            $productowner = Mage::getModel('marketplace/product')->isCustomerProduct($parentIds);

                        }
                    }
        		}
        		$customer_id = Mage::getSingleton("customer/session")->getId();
	        	$model = Mage::getModel("mprmasystem/rmarequest");
	            $model->setOrderid($data["order"]);
	            $model->setItemid($data["wk_rs_pro_check"]);
	            $model->setSellerid($productowner['userid']);
	            $model->setAdditionalinfo($data["wk_rs_additional_info"]);
	            $model->setReason($data["wk_rs_reason_details"]);
	            $model->setCreatedAt(time());
	            if($data["delivery_status"] == 1){
		            $model->setCustomerstatus($data["delivery_status"]);
		            $model->setConsignmentNo($data["consignment_no"]);
		        }
	            $model->setCustomerid($customer_id);
	            $lastid = $model->save()->getId();
	            if($lastid > 0)
	            	Mage::getModel("mprmasystem/rmamail")->NewRmaMail($data,$lastid);
        		$ext_array = array("jpg","JPG","jpeg","JPEG","gif","GIF","png","PNG","bmp","BMP");
	            if($_FILES["wk_rs_images"]["tmp_name"][0] != ""){
		            $path = Mage::getBaseDir("media").DS."mprmasystem".DS.$lastid.DS;
        			$file = new Varien_Io_File();
        			$file->mkdir($path);
		            foreach($_FILES["wk_rs_images"]["tmp_name"] as $key => $value){
		            	$ext = explode(".",$_FILES["wk_rs_images"]["name"][$key]);
		            	if(in_array(end($ext), $ext_array))
		            		move_uploaded_file($value, $path."/File-".time().$_FILES["wk_rs_images"]["name"][$key]);
		            	else
		            		$error = true;
		            }
		        }
		        if($error == true)
		        	Mage::getSingleton("core/session")->addNotice(Mage::helper("mprmasystem")->__("All files may not be uploaded"));
	        	Mage::getSingleton("core/session")->addSuccess(Mage::helper("mprmasystem")->__("RMA Saved Successfully"));
		    	$this->_redirect("*/");
		    }
		    else{
	    		Mage::getSingleton("core/session")->addError(Mage::helper("mprmasystem")->__("Unable to save"));
		    	$this->_redirect("*/index/new");
		    }
        }

        protected function viewAction(){
        	$id = $this->getRequest()->getParam("id");
        	$RMA = Mage::getModel("mprmasystem/rmarequest")->load($id);
        	if($RMA->getCustomerid() == Mage::getSingleton("customer/session")->getId()){
        		$this->loadLayout(array("default","mprmasystem_view"));
				$this->getLayout()->getBlock("head")->setTitle(Mage::helper("mprmasystem")->__("RMA Details"));
				$this->renderLayout();
        	}
        	else{
        		Mage::getSingleton("core/session")->addError(Mage::helper("mprmasystem")->__("Sorry You Are Not Authorised to view this RMA request"));
		    	$this->_redirect("*/*/");
        	}
        }

        protected function cancelAction(){
        	$id = $this->getRequest()->getParam("id");
        	$RMA = Mage::getModel("mprmasystem/rmarequest")->load($id);
        	if($RMA->getCustomerid() == Mage::getSingleton("customer/session")->getId()){
        		Mage::getModel("mprmasystem/rmastatus")->setCancelbycustomer($id);
        		Mage::getSingleton("core/session")->addSuccess(Mage::helper("mprmasystem")->__("RMA with id ").$id.Mage::helper("mprmasystem")->__(" has been cancelled successully"));
		    	$this->_redirect("*/");
        	}
        	else{
        		Mage::getSingleton("core/session")->addError(Mage::helper("mprmasystem")->__("Sorry You Are Not Authorised to cancel this RMA request"));
		    	$this->_redirect("*/*/");
        	}
        }

        protected function updatecustomerstatusAction(){
        	$data = $this->getRequest()->getParams();
        	$model = Mage::getModel("mprmasystem/rmarequest")->load($data["id"]);
        	$model->setCustomerstatus(1);
        	$model->setConsignmentNo($data["con_num"]);
        	$lastid = $model->save()->getId();
        	if($lastid > 0)
        		Mage::getModel("mprmasystem/rmamail")->ConsignmentMail($data["id"],$data["con_num"],1);
        }

        protected function saveconversationAction(){
        	$data = $this->getRequest()->getParams();
        	$model = Mage::getModel("mprmasystem/conversation");
        	$model->setRmaid($data["rmaid"]);
        	$model->setMessage($data["message"]);
        	$model->setCreatedAt(time());
        	$model->setSender(Mage::getSingleton("customer/session")->getCustomer()->getName());   
        	$model->setSendertype($data['sendertype']);   
        	$model->save();
        	if($data["reopen"]){
        		Mage::getModel("mprmasystem/rmastatus")->setPending($data["rmaid"]);
        		Mage::getSingleton("core/session")->addSuccess(Mage::helper("mprmasystem")->__("RMA Reopened Successfully"));
        		Mage::getModel("mprmasystem/rmamail")->ReopnenNMsgMail($data["rmaid"]);
        	}
        	else{
        		Mage::getModel("mprmasystem/rmamail")->MsgToAdminMail($data["rmaid"]);
        	}
        	Mage::getSingleton("core/session")->addSuccess(Mage::helper("mprmasystem")->__("Message Saved Successfully"));
	    	$this->_redirect("*/index/view/", array("id" => $data["rmaid"]));
        }

        protected function marksolveAction(){
        	$data = $this->getRequest()->getPost();
        	if($data){
	        	Mage::getModel("mprmasystem/rmastatus")->setSolved($data["rmaid"]);
	        	Mage::getModel("mprmasystem/rmamail")->SolvedRMAMail($data["rmaid"]);
	        	Mage::getSingleton("core/session")->addSuccess(Mage::helper("mprmasystem")->__("Congratulations Your RMA is being closed now"));
		    	$this->_redirect("*/index/view/", array("id" => $data["rmaid"]));
	    	}
	    	else{
	    		Mage::getSingleton("core/session")->addNotice(Mage::helper("mprmasystem")->__("Sorry something went wrong"));
		    	$this->_redirect("*/*/");
	    	}
        }

        public function imageuploadAction()	{
			$id = $this->getRequest()->getParam("id");
			$path = Mage::getBaseDir("media")."/mprmasystem/".$id."/";
			$file = new Varien_Io_File();
			$file->mkdir($path);
			$filenames = array();
			foreach($_FILES["wk_rs_images"]["tmp_name"] as $key => $value) {
				$filename = "File-".time().$_FILES["wk_rs_images"]["name"][$key];
				move_uploaded_file($value, $path.$filename);
				array_push($filenames,array("file" => $filename));
			}
			echo json_encode($filenames);
		}

	}