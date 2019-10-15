<?php
class Webkul_Marketplace_Block_Feedback extends Mage_Core_Block_Template
{	
	public function __construct(){		
		parent::__construct();	
		//echo "<pre>";
    	$userId=$this->getProfileDetail()->getMageuserid();
		$collection = Mage::getModel('marketplace/feedback')->getCollection()
															   ->addFieldToFilter('status',array('neq'=>0))
															   ->addFieldToFilter('proownerid',array('eq'=>$userId));

		$this->setCollection($collection);
	}
	
	public function _prepareLayout()
    {
		parent::_prepareLayout(); 
		$pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'all'));
        $pager->setCollection($this->getCollection());
		$temp=explode('/feedback',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		$temp1 = explode('?', $temp[1]);
		$this->getLayout()->getBlock('head')->setTitle($temp1[0].'\'s Feedback');
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }
	 public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }
    public function getCustomerpartner(){ 
        if (!$this->hasData('marketplace')) {
            $this->setData('marketplace', Mage::registry('marketplace'));
        }
		$id=$_GET["id"];
		return $id;   
    }
	
	public function getProfileDetail(){
		$temp=explode('/feedback',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		//echo $temp[1]; die();
		if($temp[1]!=''){
			$temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ return $seller;}
		}
	}
	public function getFeed(){
		$temp=explode('/feedback',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		if($temp[1]!=''){
			$temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ $id=$seller->getMageuserid();}
		}
		return Mage::getModel('marketplace/feedback')->getTotal($id);
	}
}