<?php
	class Webkul_Mprmasystem_Block_Adminhtml_Allrma_Edit_Tab_Conversation extends Mage_Adminhtml_Block_Widget_Grid {

	    public function __construct() {
	        parent::__construct();
	        $this->setId("productGrid");
	        $this->setUseAjax(true);
	        $this->setSaveParametersInSession(true);
	    }

	    protected function _prepareCollection()    {
        	$collection = Mage::getModel("mprmasystem/conversation")->getCollection()->addFieldToFilter("rmaid",$this->getRequest()->getParam("id"));
	        $this->setCollection($collection);
	        parent::_prepareCollection();
	        return $this;
	    }

    	protected function _prepareColumns()    {

	        $this->addColumn("id",array(
	            "header"  =>  Mage::helper("mprmasystem")->__("Id"),
	            "width"   =>  "50px",
	            "align"   =>  "center",
	            "index"   =>  "id"
	        ));

	        $this->addColumn("sender",array(
	            "header"  =>  Mage::helper("mprmasystem")->__("Sender Name"),
	            "index"   =>  "sender",
	            "width"	  =>  "200px"	
	        ));

	        $this->addColumn("message",array(
	            "header"  =>   Mage::helper("mprmasystem")->__("Message"),
	            "align"   =>   "left",
	            "index"   =>   "message"
	        ));

	        $this->addColumn("created_at", array(
                "header"    =>  Mage::helper("mprmasystem")->__("Date"),
                "align"     =>  "left",
                "width"     =>  "150px",
                "index"     =>  "created_at",
                "type"      =>  "datetime"
            ));
	    
	        return parent::_prepareColumns();
	    }	    

	    public function getGridUrl()    {
	        return $this->getUrl("*/*/conversationgrid",array("_current"=>true));
	    }

	}