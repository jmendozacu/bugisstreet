<?php
	class Webkul_Mprmasystem_Block_Adminhtml_Rmareason extends Mage_Adminhtml_Block_Widget_Grid_Container {

	    public function __construct() {
	        $this->_controller = "adminhtml_rmareason";
	        $this->_blockGroup = "mprmasystem";
	        $this->_headerText = Mage::helper("mprmasystem")->__("RMA Reasons");
	        $this->_addButton("btnAdd", array(
		        "label" 	=> Mage::helper("mprmasystem")->__("Add New Reason"),
		        "onclick" 	=> "setLocation('".$this->getUrl("*/*/newreason")."')",
		        "class" 	=> "add"
		    ));
	        parent::__construct();
	        $this->_removeButton("add");
	    }

	}