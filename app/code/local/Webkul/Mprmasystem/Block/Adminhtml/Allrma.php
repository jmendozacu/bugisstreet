<?php
	class Webkul_Mprmasystem_Block_Adminhtml_Allrma extends Mage_Adminhtml_Block_Widget_Grid_Container {

	    public function __construct() {
	        $this->_controller = "adminhtml_allrma";
	        $this->_blockGroup = "mprmasystem";
	        $this->_headerText = Mage::helper("mprmasystem")->__("All RMA");
	        parent::__construct();
	        $this->_removeButton("add");
	    }

	}