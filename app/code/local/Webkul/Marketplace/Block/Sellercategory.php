<?php
class Webkul_Marketplace_Block_Sellercategory extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
	
	public function getCategoryList(){
		$sellerid=$this->getProfileDetail()->getmageuserid();
		$products=Mage::getModel('marketplace/product')->getCollection()
								->addFieldToFilter('userid',array('eq'=>$sellerid))
								->addFieldToFilter('status', array('neq' => 2))
								->addFieldToSelect('mageproductid');
		$eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $pro_att_id = $eavAttribute->getIdByCode("catalog_category","name");

        $storeId = Mage::app()->getStore()->getStoreId();
        //if(!$_GET["c"]){
        	$parentid = Mage::app()->getStore($storeId)->getRootCategoryId();
        // }else{
        // 	$parentid = $_GET["c"];
        // }

		$prefix = Mage::getConfig()->getTablePrefix();
		$products->getSelect()
        ->join(array("ccp" => $prefix."catalog_category_product"),"ccp.product_id = main_table.mageproductid",array("category_id" => "category_id"))
        ->join(array("cce" => $prefix."catalog_category_entity"),"cce.entity_id = ccp.category_id",array("parent_id" => "parent_id"))->where("cce.parent_id = '".$parentid."'")
        ->columns('COUNT(*) AS countCategory')
        ->group('category_id')
        ->join(array("ce1" => $prefix."catalog_category_entity_varchar"),"ce1.entity_id = ccp.category_id",array("name" => "value"))->where("ce1.attribute_id = ".$pro_att_id)
        ->order('name');
        return $products;
	}
	
	public function getProfileDetail(){
		$temp=explode('/collection',Mage::helper('core/url')->getCurrentUrl());
		$temp=explode('/',$temp[1]);
		if($temp[1]!=''){
			$temp1 = explode('?', $temp[1]);
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
			foreach($data as $seller){ return $seller;}
		}
	}
    
}