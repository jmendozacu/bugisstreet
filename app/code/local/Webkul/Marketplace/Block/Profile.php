<?php
class Webkul_Marketplace_Block_Profile extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		$partner=$this->getProfileDetail();
		if($partner->getShoptitle()!='')
			$this->getLayout()->getBlock('head')->setTitle($partner->getShoptitle());
		else
			$this->getLayout()->getBlock('head')->setTitle($partner->getProfileurl());
		$this->getLayout()->getBlock('head')->setKeywords($partner->getMetaKeyword());	
		$this->getLayout()->getBlock('head')->setDescription($partner->getMetaDescription());
		return parent::_prepareLayout();
    }
    
	public function getProfileDetail(){
		$temp=explode('/profile/',Mage::helper('core/url')->getCurrentUrl());
		if($temp[1]!=''){
			$temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ return $seller;}
		}
	}
	
	public function getFeed(){
		$temp=explode('/profile/',Mage::helper('core/url')->getCurrentUrl());
		if($temp[1]!=''){
			$temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ $id=$seller->getMageuserid();}
		}
		return Mage::getModel('marketplace/feedback')->getTotal($id);
	}
	
	public function getBestsellProducts(){
		$temp=explode('/profile/',Mage::helper('core/url')->getCurrentUrl());
		$products=array();
		if($temp[1]!=''){
			$temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ $id=$seller->getmageuserid();}
			$data=Mage::getModel('marketplace/product')->getCollection()
									->addFieldToFilter('userid',array('eq'=>$id))
									->addFieldToFilter('status',array('neq'=>2))->setPageSize(9)
									->setOrder('mageproductid', 'DESC');
		   foreach($data as $data1){
				array_push($products,$data1->getMageproductid());
			}		
		}
		return $products;
	}
    /* public function getCustomerpartner(){ 
        if (!$this->hasData('marketplace')) {
            $this->setData('marketplace', Mage::registry('marketplace'));
        } 
    }*/
}