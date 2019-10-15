<?php

require_once 'Mage/Customer/controllers/AccountController.php';
class Webkul_Marketplace_MarketplaceaccountController extends Mage_Customer_AccountController{	
    public function indexAction(){		
		$this->loadLayout();     
		$this->renderLayout();
    }
    
    public function newAction(){
		$set=$this->getRequest()->getParam('set');
		$type=$this->getRequest()->getParam('type');
		if(isset($set) && isset($type)){
			$allowedsets=explode(',',Mage::getStoreConfig('marketplace/marketplace_options/attributesetid'));
			$allowedtypes=explode(',',Mage::getStoreConfig('marketplace/marketplace_options/allow_for_seller'));
			if(!in_array($type,$allowedtypes) || !in_array($set,$allowedsets)){
				Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Product Type Invalide Or Not Allowed'));
			    $this->_redirect('marketplace/marketplaceaccount/new/');
			}
			Mage::getSingleton('core/session')->setAttributeSet($set);
			switch($type){
				case "simple":
					$this->loadLayout(array('default','marketplace_account_simpleproduct'));
					$this->getLayout()->getBlock('head')->setTitle(Mage::helper('marketplace')->__('MarketPlace Product Type: Simple Product'));
					break;
				case "downloadable":
					$this->loadLayout(array('default','marketplace_account_downloadableproduct'));
					$this->getLayout()->getBlock('head')->setTitle(Mage::helper('marketplace')->__('MarketPlace Product Type: Downloabable Product'));
					break;
				case "virtual":
					$this->loadLayout(array('default','marketplace_account_virtualproduct'));
					$this->getLayout()->getBlock('head')->setTitle(Mage::helper('marketplace')->__('MarketPlace Product Type: Virtual Product'));
					break;
				case "configurable":
					$this->loadLayout(array('default','marketplace_account_configurableproduct'));
					$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('MarketPlace Product Type: Configurable Product'));
					break;
			}
			Mage::dispatchEvent('mp_bundalproduct',array('layout'=>$this,'type'=>$type));
			Mage::dispatchEvent('mp_groupedproduct',array('layout'=>$this,'type'=>$type));
			
			$this->_initLayoutMessages('catalog/session');
			$this->renderLayout();
		}else{
		  $this->loadLayout(array('default','marketplace_marketplaceaccount_newproduct'));     
		  $this->renderLayout();
		}
	}


	// Upload images by plupload
	public function uploadImagesAction(){
		$customerData = Mage::getSingleton('customer/session')->getCustomer();
		$lastId =  $customerData->getId();
		if(!is_dir(Mage::getBaseDir().'/media/marketplace/')){
			mkdir(Mage::getBaseDir().'/media/marketplace/', 0755);
		}
		if(!is_dir(Mage::getBaseDir().'/media/marketplace/'.$lastId.'/')){
			mkdir(Mage::getBaseDir().'/media/marketplace/'.$lastId.'/', 0755);
		}
		$target =Mage::getBaseDir().'/media/marketplace/'.$lastId.'/';

		$arr_images = $_FILES['file'];
		if(isset($_FILES) && count($_FILES) > 0){
				if($arr_images['tmp_name'] != ''){
					$splitname = explode('.', $arr_images['name']);
					$splitname[0] = str_replace('-', '', $splitname[0]);
					$image_name = preg_replace('/[^A-Za-z0-9\-]/', '', $splitname[0]);
					$target1 = $target.$image_name.".".$splitname[1];
					$web_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'/media/marketplace/'.$lastId.'/'.$image_name.".".$splitname[1];
					move_uploaded_file($arr_images['tmp_name'],$target1);
				}
			}
		$result = array(
			'real_url' => $target1,
			'web_url' => $web_url
		);
		echo json_encode($result); die;
		//echo $target1;
	}

    public function categorytreeAction(){
		$data = $this->getRequest()->getParams();
		$category_model = Mage::getModel("catalog/category");
		$category = $category_model->load($data["CID"]);
		$children = $category->getChildren();
		$all = explode(",",$children);$result_tree = "";$ml = $data["ML"]+20;$count = 1;$total = count($all);
		$plus = 0;
		
		foreach($all as $each){
			$count++;
			$_category = $category_model->load($each);
			if(count($category_model->getResource()->getAllChildren($category_model->load($each)))-1 > 0){
				$result[$plus]['counting']=1;  			
			}else{
				$result[$plus]['counting']=0;
			}
			$result[$plus]['id']= $_category['entity_id'];
			$result[$plus]['name']= $_category->getName();

			$categories = explode(",",$data["CATS"]);
			if($data["CATS"] && in_array($_category["entity_id"],$categories)){
				$result[$plus]['check']= 1;
			}else{
				$result[$plus]['check']= 0;
			}
			$plus++;
		}
		echo json_encode($result);
	}
	
	/*save All product*/
	public function simpleproductAction(){
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
             $this->_redirect('marketplace/marketplaceaccount/new/');
            }
			 
			list($data, $errors) = $this->validatePost();
			$wholedata=$this->getRequest()->getParams();
            $check = $wholedata["special_from_date"];
            if($check!=""){
                $arr=explode(':',$check);
                $wholedata["special_from_date"] = $arr[0].":".$arr[2].":".$arr[1];

            }
            $check1 = $wholedata["special_to_date"];
            if($check1!=""){
                $arr1=explode(':',$check1);
                $wholedata["special_to_date"] = $arr1[0].":".$arr1[2].":".$arr1[1];
            }

			if(empty($errors)){		
				$id = Mage::getModel('marketplace/product')->saveSimpleNewProduct($wholedata);
               // Mage::getModel('marketplace/product')->approveSimpleProduct($id);
				$status=Mage::getStoreConfig('marketplace/marketplace_options/partner_approval');
				if($status==1){
					$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
					$customer = Mage::getModel('customer/customer')->load($vendorId);
					$cfname=$customer->getFirstname()." ".$customer->getLastname();
					$cmail=$customer->getEmail();
					$catagory_model = Mage::getModel('catalog/category');
					$categoriesy = $catagory_model->load($wholedata['category'][0]);
					$categoryname=$categoriesy->getName();
					$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
					$emailTempVariables = array();
					$adminname = 'Administrators';
					$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
					$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
					$emailTempVariables['myvar1'] = $wholedata['name'];
					$emailTempVariables['myvar2'] =$categoryname;
					$emailTempVariables['myvar3'] = $adminname;
					$emailTempVariables['myvar4'] = Mage::helper('marketplace')->__('I would like to inform you that recently i have added a new product in the store.');
					$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
					$emailTemp->setSenderName($cfname);
					$emailTemp->setSenderEmail($cmail);
					$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
				}
			}else{
				foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
				$_SESSION['new_products_errors'] = $data;
			}
			if (empty($errors))
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Your product was successfully Saved'));
			    $this->_redirect('marketplace/marketplaceaccount/new/');
		}
		else{
			 $this->_redirect('marketplace/marketplaceaccount/new/');
		}
	}
	public function virtualproductAction() {
		if($this->getRequest()->isPost()){
			if(!$this->_validateFormKey()) {
				$this->_redirect('marketplace/marketplaceaccount/new/');
			}
			list($data, $errors) = $this->validatePost();
			$wholedata=$this->getRequest()->getParams();
			if(empty($errors)){		
				Mage::getModel('marketplace/product')->saveVirtualNewProduct($wholedata);
				$status=Mage::getStoreConfig('marketplace/marketplace_options/partner_approval');
				if($status==1){
					$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
				    $customer = Mage::getModel('customer/customer')->load($vendorId);
					$cfname=$customer->getFirstname()." ".$customer->getLastname();
					$cmail=$customer->getEmail();
					$catagory_model = Mage::getModel('catalog/category');
					$categoriesy = $catagory_model->load($wholedata['category'][0]);
					$categoryname=$categoriesy->getName();
					$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
					$emailTempVariables = array();
					$adminname = 'Administrators';
					$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
					$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
					$emailTempVariables['myvar1'] = $wholedata['name'];
					$emailTempVariables['myvar2'] =$categoryname;
					$emailTempVariables['myvar3'] = $adminname;
					$emailTempVariables['myvar4'] = Mage::helper('marketplace')->__('I would like to inform you that recently i have added a new product in the store.');
					$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
					$emailTemp->setSenderName($cfname);
					$emailTemp->setSenderEmail($cmail);
					$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
				}
			}else{
				foreach ($errors as $message) {$this->_getSession()->addError($message);}
				$_SESSION['new_products_errors'] = $data;
			}
			if (empty($errors))
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Your product was successfully Saved'));
				 $this->_redirect('marketplace/marketplaceaccount/new/');
		}
		else{
			 $this->_redirect('marketplace/marketplaceaccount/new/');
		}
	}
	public function downloadableproductAction() {
		if($this->getRequest()->isPost()){ 
			 if (!$this->_validateFormKey()) {
				 $this->_redirect('marketplace/marketplaceaccount/new/');
             }
			list($data, $errors) = $this->validatePost();
			$wholedata=$this->getRequest()->getParams();
			if(empty($errors)){		
				Mage::getModel('marketplace/product')->saveDownloadableNewProduct($wholedata);
				$status=Mage::getStoreConfig('marketplace/marketplace_options/partner_approval');
				if($status==1){
					$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
				    $customer = Mage::getModel('customer/customer')->load($vendorId);
					$cfname=$customer->getFirstname()." ".$customer->getLastname();
					$cmail=$customer->getEmail();
					$catagory_model = Mage::getModel('catalog/category');
					$categoriesy = $catagory_model->load($wholedata['category'][0]);
					$categoryname=$categoriesy->getName();
					$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
					$emailTempVariables = array();
					$adminname = 'Administrators';
					$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
					$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
					$emailTempVariables['myvar1'] = $wholedata['name'];
					$emailTempVariables['myvar2'] =$categoryname;
					$emailTempVariables['myvar3'] = $adminname;
					$emailTempVariables['myvar4'] = Mage::helper('marketplace')->__('I would like to inform you that recently i have added a new product in the store.');
					$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
					$emailTemp->setSenderName($cfname);
					$emailTemp->setSenderEmail($cmail);
					$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
				}
			}else{
				foreach ($errors as $message) {$this->_getSession()->addError($message);}
				$_SESSION['new_products_errors'] = $data;
			}
			if (empty($errors))
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Your product was successfully Saved'));
				return $this->_redirect('marketplace/marketplaceaccount/new/');
		}
		else{
			return $this->_redirect('marketplace/marketplaceaccount/new/');
		}
	}
	public function configurableproductAction() {
		$wholedata=$this->getRequest()->getParam('attribute');
		$magentoProductModel = Mage::getModel('catalog/product');
		$this->_redirect('marketplace/marketplaceaccount/addconfigurableproduct');
	}
	public function configurableproductattrAction(){
		$this->loadLayout( array('default','marketplace_account_configurableproductattr'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('Configurable Product Attribute'));
    	$this->renderLayout();
	}

	public function vieworderAction(){
		$available_seller_item = 0;
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$seller_orderslist=Mage::getModel('marketplace/saleslist')->getCollection()
									 ->addFieldToFilter('mageproownerid',array('eq'=>$customerid))
									 ->addFieldToFilter('mageorderid',array('eq'=>$this->getRequest()->getParam('id')));
		foreach($seller_orderslist as $seller_item){
			$available_seller_item = 1;
		}
		if($available_seller_item){
			$this->loadLayout( array('default','marketplace_marketplaceaccount_vieworder'));
	        $this->_initLayoutMessages('customer/session');
	        $this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle($this->__('View Order Details'));
	    	$this->renderLayout();
	    }else{
	    	$this->_redirect('marketplace/marketplaceaccount/myorderhistory');
	    }
	}
	public function printorderAction(){
		$available_seller_item = 0;
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$seller_orderslist=Mage::getModel('marketplace/saleslist')->getCollection()
									 ->addFieldToFilter('mageproownerid',array('eq'=>$customerid))
									 ->addFieldToFilter('mageorderid',array('eq'=>$this->getRequest()->getParam('id')));
		foreach($seller_orderslist as $seller_item){
			$available_seller_item = 1;
		}
		if($available_seller_item){
			$this->loadLayout( array('default','marketplace_marketplaceaccount_printorder'));
	        $this->_initLayoutMessages('customer/session');
	        $this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle($this->__('Print Order Details'));
	    	$this->renderLayout();
    	}else{
	    	$this->_redirect('marketplace/marketplaceaccount/myorderhistory');
	    }
	}
	
	public function addconfigurableproductAction(){
		return $this->_redirect('marketplace/marketplaceaccount/new/');
		/*$this->loadLayout( array('default','marketplace_account_configurableproduct'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('Add Configurable Product'));
    	$this->renderLayout();*/
	}

	public function newattributeAction(){
		$this->loadLayout( array('default','marketplace_account_newattribute'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__(' Manage Attribute'));
    	$this->renderLayout();
	}
	
	public function createattributeAction() {
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
              return $this->_redirect('marketplace/marketplaceaccount/newattribute/');
             }
			
			$wholedata=$this->getRequest()->getParams();
			$attributes = Mage::getModel('catalog/product')->getAttributes();

		    foreach($attributes as $a){
		            $allattrcodes = $a->getEntityType()->getAttributeCodes();
		    }
		    if(in_array($wholedata['attribute_code'], $allattrcodes)){
		    	Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Attribute Code already exists'));
				$this->_redirect('marketplace/marketplaceaccount/newattribute/');
		    }else{
				list($data, $errors) = $this->validatePost();
				if(array_key_exists('attroptions', $wholedata)){
					foreach( $wholedata['attroptions'] as $c){
						$data1['.'.$c['admin'].'.'] = array($c['admin'],$c['store']);	
					}
				}else{
					$data1=array();
				}
				
				$_attribute_data = array(
									'attribute_code' => $wholedata['attribute_code'],
									'is_global' => '1',
									'frontend_input' => $wholedata['frontend_input'],
									'default_value_text' => '',
									'default_value_yesno' => '0',
									'default_value_date' => '',
									'default_value_textarea' => '',
									'is_unique' => '0',
									'is_required' => '0',
									'apply_to' => '0',
									'is_configurable' => '1',
									'is_searchable' => '0',
									'is_visible_in_advanced_search' => '1',
									'is_comparable' => '0',
									'is_used_for_price_rules' => '0',
									'is_wysiwyg_enabled' => '0',
									'is_html_allowed_on_front' => '1',
									'is_visible_on_front' => '0',
									'used_in_product_listing' => '0',
									'used_for_sort_by' => '0',
									'frontend_label' => $wholedata['attribute_code']
								);
				$model = Mage::getModel('catalog/resource_eav_attribute');
				if (!isset($_attribute_data['is_configurable'])) {
					$_attribute_data['is_configurable'] = 0;
				}
				if (!isset($_attribute_data['is_filterable'])) {
					$_attribute_data['is_filterable'] = 0;
				}
				if (!isset($_attribute_data['is_filterable_in_search'])) {
					$_attribute_data['is_filterable_in_search'] = 0;
				}
				if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
					$_attribute_data['backend_type'] = $model->getBackendTypeByInput($_attribute_data['frontend_input']);
				}
				$defaultValueField = $model->getDefaultValueByInput($_attribute_data['frontend_input']);
				if ($defaultValueField) {
					$_attribute_data['default_value'] = $this->getRequest()->getParam($defaultValueField);
				}
				$model->addData($_attribute_data);
				$data['option']['value'] = $data1;
				if($wholedata['frontend_input'] == 'select' || $wholedata['frontend_input'] == 'multiselect')
					$model->addData($data);
				$model->setAttributeSetId($wholedata['attribute_set_id']);
				$model->setAttributeGroupId($wholedata['AttributeGroupId']);
				$entityTypeID = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
				$model->setEntityTypeId($entityTypeID);
				$model->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
				$model->setIsUserDefined(1);
				$model->save();
				Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Attribute Created Successfully'));
				$this->_redirect('marketplace/marketplaceaccount/newattribute/');
			}
		}
	}

	public function quickcreateAction() {
		 if (!$this->_validateFormKey()) {
           return $this->_redirect('marketplace/marketplaceaccount/myproductslist/');
         }
		$wholedata=$this->getRequest()->getParams();
		$id = $wholedata['mainid'];
		try{
			Mage::getModel('marketplace/product')->quickcreate($wholedata);
			Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Associate Product created Successfully'));
		}
		catch(Exception $e){
			Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Associate Product already existed'));
		}
		$this->_redirect('marketplace/marketplaceaccount/configurableassociate',array('id'=>$id));
	}
	public function assignassociateAction() {
		$wholedata=$this->getRequest()->getParams();
	    Mage::getModel('marketplace/product')->editassociate($wholedata);
	    Mage::getModel('marketplace/product')->saveassociate($wholedata);
	    $id = $wholedata['mainid'];
		Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Product has been assigned successfully'));
		$this->_redirect('marketplace/marketplaceaccount/configurableassociate',array('id'=>$id));
	}

	public function configproductAction() {
		if($this->getRequest()->isPost()){
			 if (!$this->_validateFormKey()) {
              return $this->_redirect('marketplace/marketplaceaccount/new/');
             }
			list($data, $errors) = $this->validatePost();
			$wholedata=$this->getRequest()->getParams();
            $check = $wholedata["special_from_date"];
            if($check!=""){
                $arr=explode(':',$check);
                $wholedata["special_from_date"] = $arr[0].":".$arr[2].":".$arr[1];

            }
            $check1 = $wholedata["special_to_date"];
            if($check1!=""){
                $arr1=explode(':',$check1);
                $wholedata["special_to_date"] = $arr1[0].":".$arr1[2].":".$arr1[1];
            }

            if(empty($errors)){
			$id =  Mage::getModel('marketplace/product')->saveConfigNewProduct($wholedata);
                //Mage::getModel('marketplace/product')->approveSimpleProduct($id);
			$status=Mage::getStoreConfig('marketplace/marketplace_options/partner_approval');
			if($status==1){
				$vendorId = Mage::getSingleton('customer/session')->getCustomer()->getId();
				$customer = Mage::getModel('customer/customer')->load($vendorId);
				$cfname=$customer->getFirstname()." ".$customer->getLastname();
				$cmail=$customer->getEmail();
				$catagory_model = Mage::getModel('catalog/category');
				$categoriesy = $catagory_model->load($wholedata['category'][0]);
				$categoryname=$categoriesy->getName();
				$emailTemp = Mage::getModel('core/email_template')->loadDefault('approveproduct');
				$emailTempVariables = array();
				$adminname = 'Administrators';
				$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
				$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
				$emailTempVariables['myvar1'] = $wholedata['name'];
				$emailTempVariables['myvar2'] =$categoryname;
				$emailTempVariables['myvar3'] =$adminname;
				$emailTempVariables['myvar4'] = Mage::helper('marketplace')->__('I would like to inform you that recently i have added a new product in the store.');
				$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
				$emailTemp->setSenderName($cfname);
				$emailTemp->setSenderEmail($cmail);
				$emailTemp->send($adminEmail,$adminname,$emailTempVariables);
			}
			}else{
				foreach ($errors as $message) {$this->_getSession()->addError($message);}
				$_SESSION['new_products_errors'] = $data;
			}
			$attr = $wholedata['attrdata'];
			Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Product has been Created Successfully'));
			$this->_redirect('marketplace/marketplaceaccount/configurableassociate',array('attr'=>$attr,'id'=>$id));
		}
		else{
			return $this->_redirect('marketplace/marketplaceaccount/new/');
		}
	}
	
	public function configurableassociateAction(){
		$this->loadLayout( array('default','marketplace_account_configurableassociate'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('Add Associate Product'));
    	$this->renderLayout();
	}
	
	public function myproductslistAction(){
		$this->loadLayout( array('default','marketplace_account_productlist'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('My Product List'));
    	$this->renderLayout();
	}
	public function becomepartnerAction(){
		if($this->getRequest()->isPost()){ 
			 if (!$this->_validateFormKey()) {
              return $this->_redirect('marketplace/marketplaceaccount/becomepartner/');
             }
			$wholedata=$this->getRequest()->getParams();
            $session = Mage::getSingleton('customer/session');
            $customer_id = $session->getId();
            $customer_data = Mage::getModel('customer/customer')->load($customer_id);
            $customer_data->setShopname($wholedata['shopname']);
            $customer_data->setContactno($wholedata['contactno']);
            $customer_data->setShopdescription($wholedata['shopdescription']);
            $customer_data->setAreacode($wholedata['areacode']);
            $customer_data->setCountryphone($wholedata['countryphone']);
            $customer_data->save();
			Mage::getModel('marketplace/product')->saveBecomePartnerStatus($wholedata);

            //Tung fix
           // $data=Mage::getSingleton('core/app')->getRequest();
            //if($data->getParam('wantpartner')==1){
                $customer = Mage::getModel('customer/session')->getCustomer();
                Mage::getModel('marketplace/userprofile')->getRegisterDetail($customer);
                $emailTemp = Mage::getModel('core/email_template')->loadDefault('partnerrequest');

                $emailTempVariables = array();
                $admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
                $adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
                $adminUsername = 'Admin';
                $emailTempVariables['myvar1'] = $customer->getName();
                $emailTempVariables['myvar2'] = Mage::getUrl('adminhtml/customer/edit', array('id' => $customer->getId()));

                $processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);

                $emailTemp->setSenderName($customer->getName());
                $emailTemp->setSenderEmail($customer->getEmail());
                $emailTemp->send($adminEmail,$customer->getName(),$emailTempVariables);
            //}
            //End fix

			Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Thank you registering as a seller with Bugis Street Online, your request is pending for approval, we will get back to you within 1 - 3 business days'));
			$this->_redirect('marketplace/marketplaceaccount/becomepartner/');
		}
		else{
			$this->loadLayout( array('default','marketplace_account_becomepartner'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('Seller Request Panel'));
			$this->renderLayout();
		}
	}
	
	public function myorderhistoryAction(){
		$this->loadLayout( array('default','marketplace_account_orderhistory'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('My Order History'));
    	$this->renderLayout();
	}
	
	public function editapprovedsimpleAction() {
		if($this->getRequest()->isPost()){
			 if (!$this->_validateFormKey()) {
              return $this->_redirect('marketplace/marketplaceaccount/myproductslist/');
             }
			
			list($data, $errors) = $this->validatePost();
			$id= $this->getRequest()->getParam('productid');
            $status = $this->getRequest()->getParam('status');
            if($status==1){
                Mage::getModel('marketplace/product')->approveSimpleProduct($id);
            }
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$collection_product = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id))->addFieldToFilter('userid',array('eq'=>$customerid));
            if(count($collection_product))
            {
				if(empty($errors)){
                    $wholedata = $this->getRequest()->getParams();
                    $check = $wholedata["special_from_date"];
                    $arr=explode(':',$check);
                    if(count($arr)=="3"){
                        $wholedata["special_from_date"] = $arr[0].":".$arr[2].":".$arr[1];
                    }
                    $check1 = $wholedata["special_to_date"];
                    $arr1=explode(':',$check1);
                    if(count($arr1)=="3"){
                        $wholedata["special_to_date"] = $arr1[0].":".$arr1[2].":".$arr1[1];
                    }
                    Mage::getSingleton('core/session')->setEditProductId($id);
					Mage::getModel('marketplace/product')->editProduct($id,$wholedata);
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Your Product Is Been Sucessfully Updated'));
					$this->_redirect('marketplace/marketplaceaccount/myproductslist/');
				}else{
					foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
					$_SESSION['new_products_errors'] = $data;
					$this->_redirect('marketplace/marketplaceaccount/editapprovedsimple',array('id'=>$id));
				}
		    }
		    else
		    {
				$this->_redirect('marketplace/marketplaceaccount/editapprovedsimple',array('id'=>$id));
			}	
		}
		else{
			$urlid=$this->getRequest()->getParam('id');
			$loadpro =Mage::getModel('catalog/product')->load($urlid);
			if($loadpro ->getTypeId()!='simple'){
				$type_id = $loadpro ->getTypeId();
				if($type_id=='virtual')
					$this->_redirect('marketplace/marketplaceaccount/editapprovedvirtual',array('id'=>$urlid));
				if($type_id=='downloadable')
					$this->_redirect('marketplace/marketplaceaccount/editapproveddownloadable',array('id'=>$urlid));	
				if($type_id=='configurable')
					$this->_redirect('marketplace/marketplaceaccount/editapprovedconfigurable',array('id'=>$urlid));
			}
			$this->loadLayout( array('default','marketplace_account_simpleproductedit'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('MarketPlace: Edit Simple Magento Product'));
			$this->renderLayout();
		}
	}
	public function editapprovedvirtualAction() {
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
				return $this->_redirect('marketplace/marketplaceaccount/myproductslist/');
			}
			list($data, $errors) = $this->validatePost();
			$id= $this->getRequest()->getParam('productid');
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$collection_product = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id))->addFieldToFilter('userid',array('eq'=>$customerid));
			if(count($collection_product)){
				if(empty($errors)){     
					Mage::getSingleton('core/session')->setEditProductId($id);
					Mage::getModel('marketplace/product')->editVirtualProduct($id,$this->getRequest()->getParams());
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Your Product Is Been Sucessfully Updated'));
					$this->_redirect('marketplace/marketplaceaccount/myproductslist/');
				}else{
					foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
					$_SESSION['new_products_errors'] = $data;
					$this->_redirect('marketplace/marketplaceaccount/editapprovedvirtual',array('id'=>$id));
				}
			}else{
				$this->_redirect('marketplace/marketplaceaccount/editapprovedvirtual',array('id'=>$id));
			}					
		}else{
			$urlid=$this->getRequest()->getParam('id');
			$loadpro =Mage::getModel('catalog/product')->load($urlid);
			if($loadpro ->getTypeId()!='virtual'){
				$type_id = $loadpro ->getTypeId();
				if($type_id=='simple')
					$this->_redirect('marketplace/marketplaceaccount/editapprovedsimple',array('id'=>$urlid));
				if($type_id=='downloadable')
					$this->_redirect('marketplace/marketplaceaccount/editapproveddownloadable',array('id'=>$urlid));	
				if($type_id=='configurable')
					$this->_redirect('marketplace/marketplaceaccount/editapprovedconfigurable',array('id'=>$urlid));
			}
			$this->loadLayout( array('default','marketplace_account_virtualproductedit'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('MarketPlace: Edit Virtual Magento Product'));
			$this->renderLayout();
			}
        }
	public function editapproveddownloadableAction() {
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
				$this->_redirect('marketplace/marketplaceaccount/myproductslist/');
			}
			list($data, $errors) = $this->validatePost();
			$id= $this->getRequest()->getParam('productid');
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$collection_product = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id))->addFieldToFilter('userid',array('eq'=>$customerid));
			if(count($collection_product)){
				if(empty($errors)){     
					Mage::getSingleton('core/session')->setEditProductId($id);
					Mage::getModel('marketplace/product')->editDownloadableProduct($id,$this->getRequest()->getParams());
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Your Product Is Been Sucessfully Updated'));
					$this->_redirect('marketplace/marketplaceaccount/myproductslist/');
				}else{
					foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
					$_SESSION['new_products_errors'] = $data;
					$this->_redirect('marketplace/marketplaceaccount/editapproveddownloadable',array('id'=>$id));
				}
			}else{
				$this->_redirect('marketplace/marketplaceaccount/editapproveddownloadable',array('id'=>$id));
			}	        
		}else{
			$urlid=$this->getRequest()->getParam('id');
			$loadpro =Mage::getModel('catalog/product')->load($urlid);
			if($loadpro ->getTypeId()!='downloadable'){
				$type_id = $loadpro ->getTypeId();
				if($type_id=='simple')
					$this->_redirect('marketplace/marketplaceaccount/editapprovedsimple',array('id'=>$urlid));
				if($type_id=='virtual')
					$this->_redirect('marketplace/marketplaceaccount/editapprovedvirtual',array('id'=>$urlid));
				if($type_id=='configurable')
					$this->_redirect('marketplace/marketplaceaccount/editapprovedconfigurable',array('id'=>$urlid));
			}
			$this->loadLayout( array('default','marketplace_account_downloadableproductedit'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('MarketPlace: Edit Downloadable Magento Product'));
			$this->renderLayout();
		}
	}
	public function editapprovedconfigurableAction() {
		if($this->getRequest()->isPost()){
			if(!$this->_validateFormKey()){
				 $this->_redirect('marketplace/marketplaceaccount/myproductslist/');
			}
			list($data, $errors) = $this->validatePost();
			$id= $this->getRequest()->getParam('productid');
            $status = $this->getRequest()->getParam('status');
            if($status==1){
                Mage::getModel('marketplace/product')->approveSimpleProduct($id);
            }
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$collection_product = Mage::getModel('marketplace/product')->getCollection()->addFieldToFilter('mageproductid',array('eq'=>$id))->addFieldToFilter('userid',array('eq'=>$customerid));
			if(count($collection_product)){
				if(empty($errors)){
                    $wholedata = $this->getRequest()->getParams();
                    $check = $wholedata["special_from_date"];
                    $arr=explode(':',$check);
                    if(count($arr)=="3"){
                        $wholedata["special_from_date"] = $arr[0].":".$arr[2].":".$arr[1];
                    }
                    $check1 = $wholedata["special_to_date"];
                    $arr1=explode(':',$check1);
                    if(count($arr1)=="3"){
                        $wholedata["special_to_date"] = $arr1[0].":".$arr1[2].":".$arr1[1];
                    }

					Mage::getSingleton('core/session')->setEditProductId($id);
					Mage::getModel('marketplace/product')->editProduct($id,$wholedata);
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Your Product Is Been Sucessfully Updated'));
					$this->_redirect('marketplace/marketplaceaccount/myproductslist/');
				}else{
					foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
					$_SESSION['new_products_errors'] = $data;
					$this->_redirect('marketplace/marketplaceaccount/editapprovedconfigurable',array('id'=>$id));
				}
			}else{
				$this->_redirect('marketplace/marketplaceaccount/editapprovedconfigurable',array('id'=>$id));
			}				
		}else{
			$urlid=$this->getRequest()->getParam('id');
			$loadpro =Mage::getModel('catalog/product')->load($urlid);
			if($loadpro ->getTypeId()!='configurable'){
				$type_id = $loadpro ->getTypeId();
				if($type_id=='simple')
					$this->_redirect('marketplace/marketplaceaccount/editapprovedsimple',array('id'=>$urlid));
				if($type_id=='virtual')
					$this->_redirect('marketplace/marketplaceaccount/editapprovedvirtual',array('id'=>$urlid));
				if($type_id=='downloadable')
					$this->_redirect('marketplace/marketplaceaccount/editapproveddownloadable',array('id'=>$urlid));
			}
			$this->loadLayout( array('default','marketplace_account_configurableproductedit'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('MarketPlace: Edit Configurable Magento Product'));
			$this->renderLayout();
		}
	}
	
	public function deleteAction(){
		$urlapp=$_SERVER['REQUEST_URI'];
		$record=Mage::getModel('marketplace/product')->deleteProduct($urlapp);
		if($record==1){
			Mage::getSingleton('core/session')->addError( Mage::helper('marketplace')->__('YOU ARE NOT AUTHORIZE TO DELETE THIS PRODUCT..'));	
		}else{
			Mage::getSingleton('core/session')->addSuccess( Mage::helper('marketplace')->__('Your Product Has Been Sucessfully Deleted From Your Account'));
		}  
		$this->_redirect('marketplace/marketplaceaccount/myproductslist/');
	}

	public function deleteassociateAction(){
		$urlapp=$_SERVER['REQUEST_URI'];
		//$id = explode('/',$urlapp );

		$data = Mage::getSingleton('core/session')->getSomeSessionVar();
		$record=Mage::getModel('marketplace/product')->deleteassociateProduct($urlapp);
		if($record==1){
			Mage::getSingleton('core/session')->addError( Mage::helper('marketplace')->__('YOU ARE NOT AUTHORIZE TO DELETE THIS ASSOCIATE PRODUCT..'));
		}else{
			Mage::getSingleton('core/session')->addSuccess( Mage::helper('marketplace')->__('Your Associate Product Has Been Sucessfully Deleted From Your Account'));
		}
		$this->_redirect('marketplace/marketplaceaccount/configurableassociate',array('id'=>$data));
	}

	public function massdeletesellerproAction(){
		if($this->getRequest()->isPost()){
			if(!$this->_validateFormKey()){
				 $this->_redirect('marketplace/marketplaceaccount/myproductslist/');
			}
			$ids= $this->getRequest()->getParam('product_mass_delete');
			$customerid=Mage::getSingleton('customer/session')->getCustomerId();
			$unauth_ids = array();
			Mage::register("isSecureArea", 1);
			Mage :: app("default") -> setCurrentStore( Mage_Core_Model_App :: ADMIN_STORE_ID );
			foreach ($ids as $id){		
				$data['id']=$id;			
				Mage::dispatchEvent('mp_delete_product', $data);		
			    $collection_product = Mage::getModel('marketplace/product')->getCollection()
			    							->addFieldToFilter('mageproductid',array('eq'=>$id))
				    						->addFieldToFilter('userid',array('eq'=>$customerid));
				if(count($collection_product)) {					
					Mage::getModel('catalog/product')->load($id)->delete();
					$collection=Mage::getModel('marketplace/product')->getCollection()
									->addFieldToFilter('mageproductid',array('eq'=>$id));
					foreach($collection as $row){
						$row->delete();
					}
				}else{
					array_push($unauth_ids, $id);
				}
			}
		}
		if(count($unauth_ids)){
			Mage::getSingleton('core/session')->addError( Mage::helper('marketplace')->__('You are not authorized to delete products with id '.implode(",", $unauth_ids)));	
		}else{
			Mage::getSingleton('core/session')->addSuccess( Mage::helper('marketplace')->__('Products has been sucessfully deleted from your account'));
		}  
		$this->_redirect('marketplace/marketplaceaccount/myproductslist/');
	}
	
	public function mydashboardAction(){
		$this->loadLayout( array('default','marketplace_account_dashboard'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('My Dashboard'));
    	$this->renderLayout();
	}
	
	public function verifyskuAction(){
		$sku=$this->getRequest()->getParam('sku');
		$id = Mage::getModel('catalog/product')->getIdBySku($sku);
		if ($id){ $avl=0; }
		else{ $avl=1; }
		echo json_encode(array("avl"=>$avl));
	}
    public function verifynameAction(){
        $name=$this->getRequest()->getParam('name');
        $collections=Mage::getModel('marketplace/userprofile')->getCollection()
        ->addFieldToFilter('shoptitle',$name);
        $count = count($collections);
        if ($count!=0){ $avl=0; }
        else{ $avl=1; }
        echo json_encode(array("avl"=>$avl));
    }
	public function deleteimageAction(){
		$data= $this->getRequest()->getParams();
		$_product = Mage::getModel('catalog/product')->load($data['pid'])->getMediaGalleryImages();
		$main = explode('/',$data['file']);
		foreach($_product as $_image) { 
			$arr = explode('/',$_image['path']);
			if(array_pop($arr) != array_pop($main)){
				$newimage = $_image['file'];
				$id = $_image['value_id'];
				break;
			}		
		}
		$mediaApi = Mage::getModel("catalog/product_attribute_media_api");
		$mediaApi->remove($data['pid'], $data['file']);
		if($newimage){
			$objprod=Mage::getModel('catalog/product')->load($data['pid']);
			$objprod->setSmallImage($newimage);
			$objprod->setImage($newimage);
			$objprod->setThumbnail($newimage);
			$objprod->save();	
		}
	}
	
	private function validatePost(){
		$errors = array();
		$data = array();
		foreach( $this->getRequest()->getParams() as $code => $value){
			switch ($code) :
				case 'name':
					if(trim($value) == '' ){$errors[] = Mage::helper('marketplace')->__('Name has to be completed');} 
					else{$data[$code] = $value;}
					break;
				case 'description':
					if(trim($value) == '' ){$errors[] = Mage::helper('marketplace')->__('Description has to be completed');} 
					else{$data[$code] = $value;}
					break;
				case 'short_description':
					if(trim($value) == ''){$errors[] = Mage::helper('marketplace')->__('Short description has to be completed');} 
					else{$data[$code] = $value;}
					break;
				case 'price':
					if(!preg_match("/^([0-9])+?[0-9.]*$/",$value)){
						$errors[] = Mage::helper('marketplace')->__('Price should contain only decimal numbers');
					}else{$data[$code] = $value;}
					break;
				case 'weight':
					if(!preg_match("/^([0-9])+?[0-9.]*$/",$value)){
						$errors[] = Mage::helper('marketplace')->__('Weight should contain only decimal numbers');
					}else{$data[$code] = $value;}
					break;
				case 'stock':
					if(!preg_match("/^([0-9])+?[0-9.]*$/",$value)){
						$errors[] = Mage::helper('marketplace')->__('Product stock should contain only an integer number');
					}else{$data[$code] = $value;}
					break;
				case 'sku_type':
					if(trim($value) == '' ){$errors[] = Mage::helper('marketplace')->__('Sku Type has to be selected');} 
					else{$data[$code] = $value;}
					break;
				case 'price_type':
					if(trim($value) == '' ){$errors[] = Mage::helper('marketplace')->__('Price Type has to be selected');} 
					else{$data[$code] = $value;}
					break;
				case 'weight_type':
					if(trim($value) == ''){$errors[] = Mage::helper('marketplace')->__('Weight Type has to be selected');} 
					else{$data[$code] = $value;}
					break;
				case 'bundle_options':
					if(trim($value) == ''){$errors[] = Mage::helper('marketplace')->__('Default Title has to be completed');} 
					else{$data[$code] = $value;}
					break;	
			endswitch;
		}
		return array($data, $errors);
	}
	public function paymentAction(){
		$wholedata=$this->getRequest()->getParams();
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$collection = Mage::getModel('marketplace/userprofile')->getCollection();
		$collection->addFieldToFilter('mageuserid',array('eq'=>$customerid));
		foreach($collection as $row){
			$id=$row->getAutoid();
		}
		$collectionload = Mage::getModel('marketplace/userprofile')->load($id);
		$collectionload->setpaymentsource($wholedata['paymentsource']);
		$collectionload->save();
		Mage::getSingleton('core/session')->addSuccess( Mage::helper('marketplace')->__('Your Payment Information Is Sucessfully Saved.'));
		$this->_redirect('marketplace/marketplaceaccount/editProfile');
	 }
	public function askquestionAction(){
		$customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$seller = Mage::getModel('customer/customer')->load($customerid);
		$email = $seller->getEmail();
		$name = $seller->getFirstname()." ".$seller->getLastname();
		$adminname = 'Administrators';
		$admin_storemail = Mage::getStoreConfig('marketplace/marketplace_options/adminemail');
		$adminEmail=$admin_storemail? $admin_storemail:Mage::getStoreConfig('trans_email/ident_general/email');
		$emailTemp = Mage::getModel('core/email_template')->loadDefault('queryadminemail');
		$emailTempVariables = array();
		$emailTempVariables['myvar1'] = $_POST['subject'];
		$emailTempVariables['myvar2'] =$name;
		$emailTempVariables['myvar3'] = $_POST['ask'];
		$processedTemplate = $emailTemp->getProcessedTemplate($emailTempVariables);
		$emailTemp->setSenderName($name);
		$emailTemp->setSenderEmail($email);
		$emailTemp->send($adminEmail,'Administrators',$emailTempVariables);
	}
	public function deleteprofileimageAction(){
		$collection = Mage::getModel('marketplace/userprofile')->getCollection();
		$collection->addFieldToFilter('mageuserid',array('eq'=>$this->_getSession()->getCustomerId()));
		foreach($collection as  $value){ 
			$data = $value; 
			$id = $value->getAutoid(); 
		}
		Mage::getModel('marketplace/userprofile')->load($id)->setBannerpic('')->save();
		echo "true";
	}
	public function deletelogoimageAction(){
		$collection = Mage::getModel('marketplace/userprofile')->getCollection();
		$collection->addFieldToFilter('mageuserid',array('eq'=>$this->_getSession()->getCustomerId()));
		foreach($collection as  $value){ 
			$data = $value; 
			$id = $value->getAutoid(); 
		}
		Mage::getModel('marketplace/userprofile')->load($id)->setLogopic('')->save();
		echo "true";
	}
	public function editprofileAction(){
		if($this->getRequest()->isPost()){
			if (!$this->_validateFormKey()) {
				return $this->_redirect('marketplace/marketplaceaccount/editProfile');
			}
			list($data, $errors) = $this->validateprofiledata();
			$fields = $this->getRequest()->getParams();
			$loid=$this->_getSession()->getCustomerId();
			$img1='';
			$img2='';
            if(empty($errors)){		
				$write = Mage::getSingleton('core/resource')->getConnection('core_write');
				$collection = Mage::getModel('marketplace/userprofile')->getCollection();
				$collection->addFieldToFilter('mageuserid',array('eq'=>$this->_getSession()->getCustomerId()));
				foreach($collection as  $value){ $data = $value; }
				$value->settwitterid($fields['twitterid']);
				$value->setfacebookid($fields['facebookid']);
				$value->setcontactnumber($fields['contactnumber']);
				$value->setbackgroundth($fields['backgroundth']);
				$value->setshoptitle($fields['shoptitle']);
				$value->setcomplocality($fields['complocality']);
				$value->setMetaKeyword($fields['meta_keyword']);
                $value->sethide($fields['hide']);
                $value->setareamobile($fields['areamobile']);
				if($fields['compdesi']){
					$fields['compdesi'] = str_replace('script', '', $fields['compdesi']);
				}
				$value->setcompdesi($fields['compdesi']);

				if(isset($fields['returnpolicy'])){
					$fields['returnpolicy'] = str_replace('script', '', $fields['returnpolicy']);
					$value->setReturnpolicy($fields['returnpolicy']);
				}				

				if(isset($fields['shippingpolicy'])){
					$fields['shippingpolicy'] = str_replace('script', '', $fields['shippingpolicy']);
					$value->setShippingpolicy($fields['shippingpolicy']);
				}				

				$value->setMetaDescription($fields['meta_description']);
				if(strlen($_FILES['bannerpic']['name'])>0){
					$extension = pathinfo($_FILES["bannerpic"]["name"], PATHINFO_EXTENSION);
					$temp = explode(".",$_FILES["bannerpic"]["name"]);
                    $img1 = $temp[0].rand(1,99999).$loid.'.'.$extension;
					$value->setbannerpic($img1);
				}
				if(strlen($_FILES['logopic']['name'])>0){
					$extension = pathinfo($_FILES["logopic"]["name"], PATHINFO_EXTENSION);
					$temp1 = explode(".",$_FILES["logopic"]["name"]);
                    $img2 = $temp1[0].rand(1,99999).$loid.'.'.$extension;
					$value->setlogopic($img2);
				}
				if (array_key_exists('countrypic', $fields)) {
					$value->setcountrypic($fields['countrypic']);
				}
                if (array_key_exists('countrymobile', $fields)) {
                    $value->setcountrymobile($fields['countrymobile']);
                }

				$value->save();
				$target =Mage::getBaseDir().'/media/avatar/';
				$targetb = $target.$img1; 
				
				move_uploaded_file($_FILES['bannerpic']['tmp_name'],$targetb);
				$targetl = $target.$img2; 
				move_uploaded_file($_FILES['logopic']['tmp_name'],$targetl);
	           try{
					if(!empty($errors)){
		                foreach ($errors as $message){$this->_getSession()->addError($message);}
		            }else{Mage::getSingleton('core/session')->addSuccess( Mage::helper('marketplace')->__('Profile information was successfully saved'));}
	                $this->_redirect('marketplace/marketplaceaccount/editProfile');
	                return;
	            }catch (Mage_Core_Exception $e){
	                $this->_getSession()->addError($e->getMessage());
	            }catch (Exception $e){
	                $this->_getSession()->addException($e,  Mage::helper('marketplace')->__('Cannot save the customer.'));
	            }
				$this->_redirect('customer/*/*');
			}else{
				foreach ($errors as $message) {Mage::getSingleton('core/session')->addError($message);}
				$_SESSION['new_products_errors'] = $data;
				$this->_redirect('marketplace/marketplaceaccount/editProfile');
			}
        }
		else{
			$this->loadLayout( array('default','marketplace_account_editaccount'));
			$this->_initLayoutMessages('customer/session');
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('Profile Information'));
			$this->renderLayout();
		}  
    }

    private function validateprofiledata(){
		$errors = array();
		$data = array();
		foreach( $this->getRequest()->getParams() as $code => $value){
			switch ($code) :
				case 'twitterid':
					if(trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)){$errors[] = Mage::helper('marketplace')->__('Twitterid cannot contain space and special charecters');} 
					else{$data[$code] = $value;}
					break;
				case 'facebookid':
					if(trim($value) != '' &&  preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)){$errors[] = Mage::helper('marketplace')->__('Facebookid cannot contain space and special charecters');} 
					else{$data[$code] = $value;}
					break;
				case 'backgroundth':
					if(trim($value) != '' && strlen($value)!=6 && substr($value, 0, 1) != "#"){$errors[] = Mage::helper('marketplace')->__('Invalid Background Color');} 
					else{$data[$code] = $value;}
					break;
			endswitch;
		}
		return array($data, $errors);
	}
	
	public function mytransactionAction(){
	   $this->loadLayout( array('default','marketplace_transaction_info'));
	   $this->_initLayoutMessages('customer/session');
       $this->_initLayoutMessages('catalog/session');
	   $this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('Transactions'));
	   $this->renderLayout();  
	}

	public function viewtransdetailsAction(){
	   $this->loadLayout( array('default','marketplace_marketplaceaccount_viewtransdetails'));
	   $this->_initLayoutMessages('customer/session');
       $this->_initLayoutMessages('catalog/session');
	   $this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('Transaction Details'));
	   $this->renderLayout();  
	}

	public function downloadtranscsvAction(){
		$id = Mage::getSingleton('customer/session')->getId();
        $transid=$this->getRequest()->getParam('transid')!=""?$this->getRequest()->getParam('transid'):"";
        $filter_data_frm=$this->getRequest()->getParam('from_date')!=""?$this->getRequest()->getParam('from_date'):"";
        $filter_data_to=$this->getRequest()->getParam('to_date')!=""?$this->getRequest()->getParam('to_date'):"";
        if($filter_data_to){
            $todate = date_create($filter_data_to);
            $to = date_format($todate, 'Y-m-d 23:59:59');
        }
        if($filter_data_frm){
            $fromdate = date_create($filter_data_frm);
           $from = date_format($fromdate, 'Y-m-d H:i:s');
        }
        $collection = Mage::getModel('marketplace/sellertransaction')->getCollection();
        $collection->addFieldToFilter('sellerid',array('eq'=>$id));
        if($transid){
            $collection->addFieldToFilter('transactionid', array('eq' => $transid));
        }
        if($from || $to){
            $collection->addFieldToFilter('created_at', array('datetime' => true,'from' => $from,'to' =>  $to));
        }
        $collection->setOrder('transid');

        $data = array();
        foreach ($collection as $transactioncoll) {
        	$data1 =array();
        	$data1['Date'] = Mage::helper('core')->formatDate($transactioncoll->getCreatedAt(), 'medium', false);
        	$data1['Transaction Id'] = Mage::helper('core')->formatDate($transactioncoll->getCreatedAt(), 'medium', false);
        	if($transactioncoll->getCustomnote()) {
				$data1['Comment Message'] = $transactioncoll->getCustomnote(); 
			}else {
		 		$data1['Comment Message'] = Mage::helper('marketplace')->__('None');
			}
        	$data1['Transaction Amount'] = Mage::helper('core')->currency($transactioncoll->getTransactionamount(), true, false);
			$data[] = $data1;
        }

	    header('Content-Type: text/csv');
	    header('Content-Disposition: attachment; filename=transactionlist.csv');
	    header('Pragma: no-cache');
	    header("Expires: 0");

	    $outstream = fopen("php://output", "w");    
	    fputcsv($outstream, array_keys($data[0]));

	    foreach($data as $result)
	    {
	        fputcsv($outstream, $result);
	    }

	    fclose($outstream);
	}
	
	public function deletelinkAction(){
		$data= $this->getRequest()->getParams();
		$_product = Mage::getModel('downloadable/link')->load($data['id'])->delete();
	}
	
	public function deletesampleAction(){
		$data= $this->getRequest()->getParams();
		$_product = Mage::getModel('downloadable/sample')->load($data['id'])->delete();
	}

	public function nicuploadscriptAction(){
		$data= $this->getRequest()->getParams();
		if(isset($_FILES['image'])){
	        $img = $_FILES['image'];
	        $imagename = rand().$img["name"];
	        $path = "nicimages/".$imagename;
	        if(!is_dir(Mage::getBaseDir().'/media/marketplace/nicimages')){
				mkdir(Mage::getBaseDir().'/media/marketplace/nicimages', 0755);
			}
			$target =Mage::getBaseDir().'/media/marketplace/nicimages/';
			$targetpath = $target.$imagename;
	        move_uploaded_file($img['tmp_name'],$targetpath);
	        $data = getimagesize($targetpath);
	        $link = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'marketplace/'.$path;
	        $res = array("upload" => array(
                        "links" => array("original" => $link),
                        "image" => array("width" => $data[0],
                                                 "height" => $data[1]
                                                )                              
                    ));
       	}
        echo json_encode($res);
	}
}
