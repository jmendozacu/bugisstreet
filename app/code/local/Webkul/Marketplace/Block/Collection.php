<?php
class Webkul_Marketplace_Block_Collection  extends Mage_Catalog_Block_Product_List
{
	//protected $_defaultToolbarBlock = 'catalog/product_list_toolbar';
	public function __construct(){
		parent::__construct();
		if(array_key_exists('c', $_GET)){
    	$cate = Mage::getModel('catalog/category')->load($_GET["c"]);
	    }	
    	$partner=$this->getProfileDetail();
        $productname=$this->getRequest()->getParam('name');
		$querydata = Mage::getModel('marketplace/product')->getCollection()
				->addFieldToFilter('userid', array('eq' => $partner->getmageuserid()))
				->addFieldToFilter('status', array('neq' => 2))
				->setOrder('mageproductid');
		$rowdata=array();		
		foreach ($querydata as  $value) {
            $stock_item_details = Mage::getModel('cataloginventory/stock_item')->loadByProduct($value->getMageproductid());
            $stock_availability = $stock_item_details->getIsInStock();
            if($stock_availability){
                $rowdata[] = $value->getMageproductid();
            }			
		}
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addAttributeToSelect('*');
		
		if(array_key_exists('c', $_GET)){
			$collection->addCategoryFilter($cate);
		}
        $collection->addAttributeToFilter('entity_id', array('in' => $rowdata));
		if((Mage::helper('core')->isModuleEnabled('Webkul_Webkulsearch')) && ($productname!='')){
            $collection->addFieldToFilter('name', array('like' => '%'.$productname.'%'));   
        }
		$this->setCollection($collection);
	}

    public function getProfileDetail2(){
        $temp=explode('seller=',Mage::helper('core/url')->getCurrentUrl());

        if($temp[1]!=''){
            $temp1 = explode('&', $temp[1]);
            $data=Mage::getModel('marketplace/userprofile')->getCollection()
                ->addFieldToFilter('profileurl',array('eq'=>$temp1[0]));
            foreach($data as $seller){

                return $seller;}
        }
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
	
    public function getDefaultDirection(){
        return 'desc';
    }
    public function getAvailableOrders(){
        //return array('price'=>'Price','name'=>'Name');
    }
    public function getSortBy(){
        return 'created_date';
    }
     public function getToolbarBlock(){
       $block = $this->getLayout()->createBlock('marketplace/toolbar', microtime());
        return $block;
    }



    /*public function getToolbarBlock()
    {
       if ($blockName = $this->getToolbarBlockName()) {
            if ($block = $this->getLayout()->getBlock($blockName)) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
        return $block;
    }*/



    public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }
 
    public function getToolbarHtml()   {
        return $this->getChildHtml('toolbar');
    }
	public function getColumnCount() {
		return 4;
    }
}
