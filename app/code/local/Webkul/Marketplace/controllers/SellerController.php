<?php
class Webkul_Marketplace_SellerController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
	public function profileAction(){
		$temp=explode('/profile',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		if($temp[1]!=''){
            $temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ 
				$id =$seller->getAutoid();
			}
		}
		if($id){
			$this->loadLayout();     
			$this->renderLayout();
		}else{
			$this->_redirect("marketplace/index");
		}		
	}
	public function collectionAction(){
		$temp=explode('/collection',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		if($temp[1]!=''){
            $temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ 
				$id =$seller->getAutoid();
			}
		}
		if($id){
			$this->loadLayout();     
			$this->renderLayout();
		}else{
			Mage::getSingleton('core/session')->addError('No Seller exist with this name.');
			$this->_redirect("marketplace/index");
		}
	}
	public function locationAction(){
		$temp=explode('/location',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		if($temp[1]!=''){
            $temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ 
				$id =$seller->getAutoid();
			}
		}
		if($id){
			$this->loadLayout();     
			$this->renderLayout();
		}else{
			Mage::getSingleton('core/session')->addError('No Seller exist with this name.');
			$this->_redirect("marketplace/index");
		}
	}
	public function feedbackAction(){
		$temp=explode('/feedback',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		if($temp[1]!=''){
            $temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ 
				$id =$seller->getAutoid();
			}
		}
		if($id){
			$this->loadLayout();     
			$this->renderLayout();
		}else{
			Mage::getSingleton('core/session')->addError('No Seller exist with this name.');
			$this->_redirect("marketplace/index");
		}
	}
	public function usernameverifyAction(){
		$profileurl=$this->getRequest()->getParam('profileurl');
		$collection=Mage::getModel('marketplace/userprofile')->getCollection()
							->addFieldToFilter('profileurl',array('eq'=>$profileurl));
		echo count($collection);
	}
	public function sendmailAction(){		
		$data = $this->getRequest()->getParams();
		Mage::dispatchEvent('mp_send_querymail', $data);
		if($data['product-id'])
			$emailTemplate = Mage::getModel('core/email_template')->loadDefault('querypartner_email');
		else
			$emailTemplate = Mage::getModel('core/email_template')->loadDefault('askquerypartner_email');			
		$emailTemplateVariables = array();
		$mail=Mage::getModel('customer/customer')->load($data['seller-id']);
		$emailTemplateVariables['myvar1'] =$mail->getName();
		$sellerEmail = $mail->getEmail();
		$emailTemplateVariables['myvar3'] =Mage::getModel('catalog/product')->load($data['product-id'])->getName();
		$emailTemplateVariables['myvar4'] =$data['ask'];
		$emailTemplateVariables['myvar6'] =$data['subject'];
		$emailTemplateVariables['myvar5'] =$data['email'];
		$myname =Mage::getSingleton('customer/session')->getCustomer()->getName() ;
		if(strlen($myname)<2){$myname="Guest";}
		$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
		$emailTemplate->setSenderName($myname);
		$emailTemplate->setSenderEmail($data['email']);
		$emailTemplate->send($sellerEmail,$myname,$emailTemplateVariables);
		echo json_encode("true");
    }
	public function newfeedbackAction(){
		 if (!$this->_validateFormKey()) {
           return $this->_redirect('marketplace/marketplaceaccount/myproductslist/');
         }
		$wholedata=$this->getRequest()->getPost();
		Mage::getModel('marketplace/feedback')->saveFeedbackdetail($wholedata);
		Mage::getSingleton('core/session')->addSuccess('Your Review was successfully saved');
		/*$this->_redirect("marketplace/seller/feedback/".$wholedata['profileurl'].'/.');*/
        $this->_redirect("review/customer/index/");
	}
}
