<?php
    class Webkul_Mprmasystem_Block_Adminhtml_Allrma_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

        public function __construct() {       
            parent::__construct();
            $this->_objectId    = "id";
            $this->_blockGroup  = "mprmasystem";
            $this->_controller  = "adminhtml_allrma";
            $this->_updateButton("save", "label", Mage::helper("mprmasystem")->__("Update Status"));
            $this->_removeButton("delete");
            $this->_removeButton("reset");
            $this->_addButton("saveandcontinue", array(
                "label"     => Mage::helper("adminhtml")->__("Update And Continue Edit"),
                "onclick"   => "saveAndContinueEdit()",
                "class"     => "save",
                    ), -100);        
            $this->_formScripts[] = "function saveAndContinueEdit(){
                                        editForm.submit($('edit_form').action+'back/edit/');
                                    }";
        }

        public function getHeaderText() {
            return Mage::helper("mprmasystem")->__("View RMA id").$this->htmlEscape(Mage::registry("mprma_data")->getId());
        }

    }